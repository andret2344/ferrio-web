<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\LanguageResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class LegacyRedirectSubscriber implements EventSubscriberInterface
{
	private const array LEGACY_PATTERNS = [
		'#^/day/(\d+)/(\d+)$#' => 'day',
		'#^/upcoming/(-?\d+)$#' => 'upcoming',
		'#^/upcoming$#' => 'upcoming_redirect',
		'#^/privacy$#' => 'privacy',
	];

	public function __construct(
		private readonly LanguageResolver $languageResolver,
	) {}

	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => ['onKernelRequest', 33],
		];
	}

	public function onKernelRequest(RequestEvent $event): void
	{
		if (!$event->isMainRequest()) {
			return;
		}

		$request = $event->getRequest();
		$path = $request->getPathInfo();

		// Skip if already has locale prefix
		if (preg_match('#^/(en|pl)/#', $path)) {
			return;
		}

		foreach (self::LEGACY_PATTERNS as $pattern => $route) {
			if (preg_match($pattern, $path)) {
				$locale = $this->languageResolver->resolve($request);
				$newPath = '/' . $locale . $path;

				// Preserve query string
				$qs = $request->getQueryString();
				$url = $newPath . ($qs ? '?' . $qs : '');

				$event->setResponse(new RedirectResponse($url, 301));
				return;
			}
		}
	}
}
