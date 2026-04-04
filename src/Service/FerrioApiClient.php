<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FerrioApiClient
{
	private const int CACHE_TTL = 86400;
	private const float HTTP_TIMEOUT = 10.0;

	public function __construct(
		private readonly HttpClientInterface $httpClient,
		private readonly CacheInterface      $cache,
		private readonly LoggerInterface     $logger,
		private readonly string              $apiBaseUrl,
	) {}

	/**
	 * @return list<array<string, mixed>>
	 */
	public function fetchHolidaysByDate(int $day, int $month, string $lang = 'en'): array
	{
		$cacheKey = "ferrio_holidays_{$lang}_{$month}_{$day}";

		return $this->cache->get($cacheKey, function (ItemInterface $item) use ($day, $month, $lang): array {
			$item->expiresAfter(self::CACHE_TTL);

			$this->logger->debug('Fetching holidays from API', [
				'month' => $month,
				'day' => $day,
				'lang' => $lang,
			]);

			$response = $this->httpClient->request('GET', $this->apiBaseUrl . '/v3/holidays', [
				'query' => [
					'lang' => $lang,
					'day' => $day,
					'month' => $month,
				],
				'timeout' => self::HTTP_TIMEOUT,
			]);

			$data = $response->toArray();

			return array_values(array_filter(
				$data,
				static fn(array $holiday): bool => empty($holiday['mature_content']),
			));
		});
	}
}
