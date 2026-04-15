<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SecurityHeaderSubscriber implements EventSubscriberInterface
{
	public const string CSP_NONCE_ATTRIBUTE = '_csp_nonce';

	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => 'onKernelRequest',
			// Run after Symfony's ResponseListener (priority 0) so Content-Type is populated.
			KernelEvents::RESPONSE => ['onKernelResponse', -10],
		];
	}

	public function onKernelRequest(RequestEvent $event): void
	{
		if (!$event->isMainRequest()) {
			return;
		}

		$event->getRequest()->attributes->set(
			self::CSP_NONCE_ATTRIBUTE,
			base64_encode(random_bytes(16)),
		);
	}

	public function onKernelResponse(ResponseEvent $event): void
	{
		if (!$event->isMainRequest()) {
			return;
		}

		$response = $event->getResponse();
		$headers = $response->headers;

		$headers->set('X-Content-Type-Options', 'nosniff');
		$headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$headers->set('Vary', 'Accept-Encoding');

		$contentType = $response->headers->get('Content-Type', '');

		// Fix PrestaSitemap's text/xml → application/xml and add XSL stylesheet
		if (str_contains($contentType, 'text/xml')) {
			$headers->set('Content-Type', 'application/xml; charset=UTF-8');

			$xml = $response->getContent();
			if ($xml !== false && str_starts_with($xml, '<?xml')) {
				$pos = strpos($xml, '?>');
				if ($pos !== false) {
					$response->setContent(
						substr($xml, 0, $pos + 2)
						. "\n<?xml-stylesheet type=\"text/xsl\" href=\"/sitemap.xsl\"?>"
						. substr($xml, $pos + 2),
					);
				}
			}

			return;
		}

		if (!str_contains($contentType, 'text/html')) {
			return;
		}

		$nonce = $event->getRequest()->attributes->get(self::CSP_NONCE_ATTRIBUTE, '');

		$headers->set('X-Frame-Options', 'DENY');
		$headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
		$headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
		$headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'unsafe-inline'; img-src 'self' https:; connect-src 'self' https://api.ferrio.app; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
	}
}
