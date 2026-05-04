<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\SitemapSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\Url;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapSubscriberTest extends TestCase
{
	private SitemapSubscriber $subscriber;
	private UrlGeneratorInterface $urlGenerator;

	protected function setUp(): void
	{
		$this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
		$this->urlGenerator->method('generate')
			->willReturnCallback(fn(string $route, array $params = []) => "https://ferrio.app/{$route}/" . implode('/', $params));

		$this->subscriber = new SitemapSubscriber($this->urlGenerator);
	}

	#[Test]
	public function subscribesToSitemapPopulateEvent(): void
	{
		$events = SitemapSubscriber::getSubscribedEvents();

		self::assertArrayHasKey(SitemapPopulateEvent::class, $events);
		self::assertSame('populate', $events[SitemapPopulateEvent::class]);
	}

	#[Test]
	public function populateAddsDayUrls(): void
	{
		$urls = [];
		$container = $this->createMock(UrlContainerInterface::class);
		$container->method('addUrl')
			->willReturnCallback(function (Url $url, string $section) use (&$urls): void {
				$urls[$section][] = $url;
			});

		$event = $this->createMock(SitemapPopulateEvent::class);
		$event->method('getUrlContainer')->willReturn($container);

		$this->subscriber->populate($event);

		// 367 day URLs (365 + 2 virtual Feb days)
		self::assertCount(367, $urls['day']);
	}

	#[Test]
	public function populateAddsUpcomingUrls(): void
	{
		$urls = [];
		$container = $this->createMock(UrlContainerInterface::class);
		$container->method('addUrl')
			->willReturnCallback(function (Url $url, string $section) use (&$urls): void {
				$urls[$section][] = $url;
			});

		$event = $this->createMock(SitemapPopulateEvent::class);
		$event->method('getUrlContainer')->willReturn($container);

		$this->subscriber->populate($event);

		// 105 upcoming URLs (weeks -52 to 52)
		self::assertCount(105, $urls['upcoming']);
	}

	#[Test]
	public function populateAddsStaticUrls(): void
	{
		$urls = [];
		$container = $this->createMock(UrlContainerInterface::class);
		$container->method('addUrl')
			->willReturnCallback(function (Url $url, string $section) use (&$urls): void {
				$urls[$section][] = $url;
			});

		$event = $this->createMock(SitemapPopulateEvent::class);
		$event->method('getUrlContainer')->willReturn($container);

		$this->subscriber->populate($event);

		// 3 static URLs (privacy, terms, apps)
		self::assertCount(3, $urls['static']);
	}
}
