<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DayResult;
use App\Entity\Holiday;
use Psr\Log\LoggerInterface;

final class HolidayConnector
{
	public const int MAX_WEEK_OFFSET = 52;

	public function __construct(
		private readonly FerrioApiClient $apiClient,
		private readonly LoggerInterface $logger,
	) {}

	/**
	 * @return list<Holiday>
	 */
	public function getHolidaysForDate(int $month, int $day, string $lang = 'en'): array
	{
		$rawHolidays = $this->apiClient->fetchHolidaysByDate($day, $month, $lang);

		return array_map(Holiday::fromApiData(...), $rawHolidays);
	}

	public function getDayResult(int $month, int $day, string $lang = 'en', ?int $daysFromToday = null): DayResult
	{
		$holidays = $this->getHolidaysForDate($month, $day, $lang);
		$formattedDate = DateFormatter::formatMonthDay($day, $month, $lang);

		return new DayResult(
			month: $month,
			day: $day,
			formattedDate: $formattedDate,
			holidays: $holidays,
			daysFromToday: $daysFromToday,
		);
	}

	public function getDayResultSafe(int $month, int $day, string $lang = 'en'): ?DayResult
	{
		try {
			return $this->getDayResult($month, $day, $lang);
		} catch (\Exception $e) {
			$this->logger->error('Failed to fetch holidays for day', [
				'month' => $month,
				'day' => $day,
				'lang' => $lang,
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	/**
	 * @return list<DayResult>|null Null on API failure
	 */
	public function getUpcomingDaysSafe(int $weekOffset, string $lang = 'en', ?\DateTimeZone $tz = null): ?array
	{
		try {
			return $this->getUpcomingDays($weekOffset, $lang, $tz);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to fetch upcoming holidays', [
				'week' => $weekOffset,
				'lang' => $lang,
				'error' => $e->getMessage(),
			]);
			return null;
		}
	}

	/**
	 * @return list<DayResult>
	 */
	public function getUpcomingDays(int $weekOffset, string $lang = 'en', ?\DateTimeZone $tz = null): array
	{
		$weekOffset = max(-self::MAX_WEEK_OFFSET, min(self::MAX_WEEK_OFFSET, $weekOffset));

		$start = $this->getStartDate($weekOffset, $tz);
		$entries = VirtualDate::generateDateEntries($start['month'], $start['day'], 7, $tz);

		$days = [];
		foreach ($entries as $entry) {
			$days[] = $this->getDayResult(
				$entry['month'],
				$entry['day'],
				$lang,
				$entry['daysFromToday'],
			);
		}

		return $days;
	}

	/**
	 * @return array{month: int, day: int}
	 */
	private function getStartDate(int $weekOffset, ?\DateTimeZone $tz = null): array
	{
		$today = new \DateTimeImmutable('now', $tz);
		$tomorrowDate = VirtualDate::nextDay(
			(int)$today->format('n'),
			(int)$today->format('j'),
		);

		if ($weekOffset === 0) {
			return $tomorrowDate;
		}

		if ($weekOffset > 0) {
			return VirtualDate::advanceEntries($tomorrowDate['month'], $tomorrowDate['day'], $weekOffset * 7);
		}

		$real = VirtualDate::toRealCalendar($tomorrowDate['month'], $tomorrowDate['day']);
		$date = new \DateTimeImmutable(sprintf('%s-%02d-%02d', $today->format('Y'), $real['month'], $real['day']), $tz);
		$date = $date->modify(($weekOffset * 7) . ' days');

		return ['month' => (int)$date->format('n'), 'day' => (int)$date->format('j')];
	}
}
