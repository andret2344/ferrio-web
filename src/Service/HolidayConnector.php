<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DayResult;
use App\Entity\Holiday;

final class HolidayConnector
{
	private const int MAX_WEEK_OFFSET = 52;

	public function __construct(
		private readonly FerrioApiClient $apiClient,
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

	/**
	 * @return list<DayResult>
	 */
	public function getUpcomingDays(int $weekOffset, string $lang = 'en'): array
	{
		$weekOffset = max(-self::MAX_WEEK_OFFSET, min(self::MAX_WEEK_OFFSET, $weekOffset));

		$start = $this->getStartDate($weekOffset);
		$entries = VirtualDate::generateDateEntries($start['month'], $start['day'], 7);

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
	private function getStartDate(int $weekOffset): array
	{
		$today = new \DateTimeImmutable();
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
		$date = new \DateTimeImmutable(date('Y') . "-{$real['month']}-{$real['day']}");
		$date = $date->modify(($weekOffset * 7) . ' days');

		return ['month' => (int)$date->format('n'), 'day' => (int)$date->format('j')];
	}
}
