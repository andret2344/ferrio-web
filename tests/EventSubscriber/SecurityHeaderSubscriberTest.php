<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\SecurityHeaderSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class SecurityHeaderSubscriberTest extends TestCase
{
	#[Test]
	public function subscribesToKernelRequestAndResponse(): void
	{
		$events = SecurityHeaderSubscriber::getSubscribedEvents();

		self::assertArrayHasKey(KernelEvents::REQUEST, $events);
		self::assertSame('onKernelRequest', $events[KernelEvents::REQUEST]);
		self::assertArrayHasKey(KernelEvents::RESPONSE, $events);
		self::assertSame('onKernelResponse', $events[KernelEvents::RESPONSE]);
	}

	#[Test]
	public function setsNonceOnMainRequest(): void
	{
		$subscriber = new SecurityHeaderSubscriber();
		$request = Request::create('/');
		$kernel = $this->createMock(HttpKernelInterface::class);

		$event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
		$subscriber->onKernelRequest($event);

		$nonce = $request->attributes->get(SecurityHeaderSubscriber::CSP_NONCE_ATTRIBUTE);
		self::assertNotNull($nonce);
		self::assertNotEmpty($nonce);
		self::assertSame(24, strlen($nonce), 'Nonce should be 16 random bytes base64-encoded (24 chars)');
	}

	#[Test]
	public function skipsNonceOnSubRequest(): void
	{
		$subscriber = new SecurityHeaderSubscriber();
		$request = Request::create('/');
		$kernel = $this->createMock(HttpKernelInterface::class);

		$event = new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);
		$subscriber->onKernelRequest($event);

		self::assertFalse($request->attributes->has(SecurityHeaderSubscriber::CSP_NONCE_ATTRIBUTE));
	}

	#[Test]
	public function setsAllSecurityHeadersOnMainRequest(): void
	{
		$subscriber = new SecurityHeaderSubscriber();
		$request = Request::create('/');
		$response = new Response();
		$kernel = $this->createMock(HttpKernelInterface::class);

		// Simulate request phase first to generate nonce
		$requestEvent = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
		$subscriber->onKernelRequest($requestEvent);

		$responseEvent = new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
		$subscriber->onKernelResponse($responseEvent);

		self::assertSame('DENY', $response->headers->get('X-Frame-Options'));
		self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
		self::assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
		self::assertStringContainsString('camera=()', $response->headers->get('Permissions-Policy'));
		self::assertStringContainsString('payment=()', $response->headers->get('Permissions-Policy'));

		$csp = $response->headers->get('Content-Security-Policy');
		self::assertStringContainsString("default-src 'self'", $csp);
		self::assertStringContainsString("script-src 'self'", $csp);

		// CSP uses nonce, not hash
		$nonce = $request->attributes->get(SecurityHeaderSubscriber::CSP_NONCE_ATTRIBUTE);
		self::assertStringContainsString("'nonce-{$nonce}'", $csp);
		self::assertStringNotContainsString("'sha256-", $csp);

		// script-src must NOT contain unsafe-inline
		preg_match('/script-src\s+([^;]+)/', $csp, $scriptSrc);
		self::assertStringNotContainsString('unsafe-inline', $scriptSrc[1] ?? '');
		self::assertStringContainsString("connect-src 'self' https://api.ferrio.app", $csp);
		self::assertStringContainsString("frame-ancestors 'none'", $csp);
		self::assertStringContainsString("base-uri 'self'", $csp);

		self::assertSame('max-age=31536000; includeSubDomains; preload', $response->headers->get('Strict-Transport-Security'));
		self::assertSame('Accept-Encoding', $response->headers->get('Vary'));
	}

	#[Test]
	public function generatesUniqueNoncePerRequest(): void
	{
		$subscriber = new SecurityHeaderSubscriber();
		$kernel = $this->createMock(HttpKernelInterface::class);

		$request1 = Request::create('/');
		$subscriber->onKernelRequest(new RequestEvent($kernel, $request1, HttpKernelInterface::MAIN_REQUEST));

		$request2 = Request::create('/');
		$subscriber->onKernelRequest(new RequestEvent($kernel, $request2, HttpKernelInterface::MAIN_REQUEST));

		self::assertNotSame(
			$request1->attributes->get(SecurityHeaderSubscriber::CSP_NONCE_ATTRIBUTE),
			$request2->attributes->get(SecurityHeaderSubscriber::CSP_NONCE_ATTRIBUTE),
		);
	}

	#[Test]
	public function skipsSubRequests(): void
	{
		$subscriber = new SecurityHeaderSubscriber();
		$response = new Response();

		$event = new ResponseEvent(
			$this->createMock(HttpKernelInterface::class),
			Request::create('/'),
			HttpKernelInterface::SUB_REQUEST,
			$response,
		);

		$subscriber->onKernelResponse($event);

		self::assertNull($response->headers->get('X-Frame-Options'));
	}
}
