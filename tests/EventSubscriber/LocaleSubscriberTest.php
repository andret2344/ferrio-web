<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\LocaleSubscriber;
use App\Service\LanguageResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class LocaleSubscriberTest extends TestCase
{
	#[Test]
	public function subscribesToKernelRequest(): void
	{
		$events = LocaleSubscriber::getSubscribedEvents();

		self::assertArrayHasKey(KernelEvents::REQUEST, $events);
	}

	#[Test]
	public function setsLocaleFromLanguageResolverWhenNoRouteLocale(): void
	{
		$subscriber = new LocaleSubscriber(new LanguageResolver());

		$request = Request::create('/');
		$request->cookies->set('language', 'pl');

		$event = new RequestEvent(
			$this->createMock(HttpKernelInterface::class),
			$request,
			HttpKernelInterface::MAIN_REQUEST,
		);

		$subscriber->onKernelRequest($event);

		self::assertSame('pl', $request->getLocale());
	}

	#[Test]
	public function defaultsToEnglishLocale(): void
	{
		$subscriber = new LocaleSubscriber(new LanguageResolver());

		$request = Request::create('/');

		$event = new RequestEvent(
			$this->createMock(HttpKernelInterface::class),
			$request,
			HttpKernelInterface::MAIN_REQUEST,
		);

		$subscriber->onKernelRequest($event);

		self::assertSame('en', $request->getLocale());
	}

	#[Test]
	public function doesNotOverrideRouteLocale(): void
	{
		$subscriber = new LocaleSubscriber(new LanguageResolver());

		$request = Request::create('/pl/day/1/1');
		$request->attributes->set('_locale', 'pl');
		$request->setLocale('pl');
		$request->cookies->set('language', 'en');

		$event = new RequestEvent(
			$this->createMock(HttpKernelInterface::class),
			$request,
			HttpKernelInterface::MAIN_REQUEST,
		);

		$subscriber->onKernelRequest($event);

		self::assertSame('pl', $request->getLocale());
	}
}
