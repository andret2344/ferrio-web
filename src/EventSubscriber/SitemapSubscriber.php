<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\HolidayConnector;
use App\Service\VirtualDate;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\GoogleMultilangUrlDecorator;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapSubscriber implements EventSubscriberInterface
{
	public function __construct(
		private readonly UrlGeneratorInterface $urlGenerator,
	) {
	}

	public static function getSubscribedEvents(): array
	{
		return [
			SitemapPopulateEvent::class => 'populate',
		];
	}

	public function populate(SitemapPopulateEvent $event): void
	{
		$this->addDayUrls($event->getUrlContainer());
		$this->addUpcomingUrls($event->getUrlContainer());
		$this->addStaticUrls($event->getUrlContainer());
	}

	private function addDayUrls(UrlContainerInterface $urls): void
	{
		for ($month = 1; $month <= 12; $month++) {
			$maxDay = VirtualDate::MONTH_DAYS[$month - 1];

			for ($day = 1; $day <= $maxDay; $day++) {
				$enUrl = $this->urlGenerator->generate('day', [
					'_locale' => 'en',
					'month' => $month,
					'day' => $day,
				], UrlGeneratorInterface::ABSOLUTE_URL);

				$urls->addUrl($this->withLangAlternates($enUrl, $month, $day, 'daily', 0.7), 'day');
			}
		}
	}

	private function addUpcomingUrls(UrlContainerInterface $urls): void
	{
		for ($week = -HolidayConnector::MAX_WEEK_OFFSET; $week <= HolidayConnector::MAX_WEEK_OFFSET; $week++) {
			$enUrl = $this->urlGenerator->generate('upcoming', [
				'_locale' => 'en',
				'week' => $week,
			], UrlGeneratorInterface::ABSOLUTE_URL);

			$priority = $week === 0 ? 0.9 : 0.5;
			$urls->addUrl($this->withLangAlternatesSimple($enUrl, 'upcoming', ['week' => $week], 'daily', $priority), 'upcoming');
		}
	}

	private function addStaticUrls(UrlContainerInterface $urls): void
	{
		$enUrl = $this->urlGenerator->generate('privacy', ['_locale' => 'en'], UrlGeneratorInterface::ABSOLUTE_URL);
		$urls->addUrl($this->withLangAlternatesSimple($enUrl, 'privacy', [], 'monthly', 0.3), 'static');
	}

	private function withLangAlternates(string $canonicalUrl, int $month, int $day, string $changeFreq, float $priority): GoogleMultilangUrlDecorator
	{
		$enUrl = $this->urlGenerator->generate('day', [
			'_locale' => 'en',
			'month' => $month,
			'day' => $day,
		], UrlGeneratorInterface::ABSOLUTE_URL);

		$plUrl = $this->urlGenerator->generate('day', [
			'_locale' => 'pl',
			'month' => $month,
			'day' => $day,
		], UrlGeneratorInterface::ABSOLUTE_URL);

		return (new GoogleMultilangUrlDecorator(new UrlConcrete($canonicalUrl, null, $changeFreq, $priority)))
			->addLink($enUrl, 'en')
			->addLink($plUrl, 'pl')
			->addLink($enUrl, 'x-default');
	}

	private function withLangAlternatesSimple(string $canonicalUrl, string $route, array $params, string $changeFreq, float $priority): GoogleMultilangUrlDecorator
	{
		$enUrl = $this->urlGenerator->generate($route, array_merge($params, ['_locale' => 'en']), UrlGeneratorInterface::ABSOLUTE_URL);
		$plUrl = $this->urlGenerator->generate($route, array_merge($params, ['_locale' => 'pl']), UrlGeneratorInterface::ABSOLUTE_URL);

		return (new GoogleMultilangUrlDecorator(new UrlConcrete($canonicalUrl, null, $changeFreq, $priority)))
			->addLink($enUrl, 'en')
			->addLink($plUrl, 'pl')
			->addLink($enUrl, 'x-default');
	}
}
