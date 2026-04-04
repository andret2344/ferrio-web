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
			KernelEvents::RESPONSE => 'onKernelResponse',
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

		$nonce = $event->getRequest()->attributes->get(self::CSP_NONCE_ATTRIBUTE, '');

		$headers = $event->getResponse()->headers;
		$headers->set('X-Frame-Options', 'DENY');
		$headers->set('X-Content-Type-Options', 'nosniff');
		$headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
		$headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
		$headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'unsafe-inline'; img-src 'self' https:; connect-src 'self' https://api.ferrio.app; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
		$headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		$headers->set('Vary', 'Accept-Encoding');
	}
}
