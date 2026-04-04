<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\LanguageResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class LocaleSubscriber implements EventSubscriberInterface
{
	public function __construct(
		private readonly LanguageResolver $languageResolver,
	) {}

	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => ['onKernelRequest', 20],
		];
	}

	public function onKernelRequest(RequestEvent $event): void
	{
		$request = $event->getRequest();

		// For routes with {_locale}, Symfony sets the locale automatically.
		// This subscriber handles routes without {_locale} (e.g. root /).
		if ($request->attributes->get('_locale') === null) {
			$request->setLocale($this->languageResolver->resolve($request));
		}
	}
}
