<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;

final class VirtualDate
{
	/** Days per month including virtual Feb 29/30 (Feb = 30). */
	public const array MONTH_DAYS = [31, 30, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

	/** Fixed non-leap year for date arithmetic (Feb cases handled separately). */
	private const string REF_YEAR = '2025';
	/**
	 * Virtual-aware next day: Feb 28→29→30→Mar 1
	 *
	 * @return array{month: int, day: int}
	 */
	public static function nextDay(int $month, int $day): array
	{
		if ($month === 2 && $day === 28) {
			return ['month' => 2, 'day' => 29];
		}
		if ($month === 2 && $day === 29) {
			return ['month' => 2, 'day' => 30];
		}
		if ($month === 2 && $day === 30) {
			return ['month' => 3, 'day' => 1];
		}

		$date = new DateTimeImmutable(self::REF_YEAR . "-{$month}-{$day}");
		$next = $date->modify('+1 day');

		return ['month' => (int)$next->format('n'), 'day' => (int)$next->format('j')];
	}

	/**
	 * Real calendar next day: Feb 28/29/30 → Mar 1 (skips virtual days)
	 *
	 * @return array{month: int, day: int}
	 */
	public static function nextRealDay(int $month, int $day): array
	{
		if ($month === 2 && $day >= 28) {
			return ['month' => 3, 'day' => 1];
		}

		$date = new DateTimeImmutable(self::REF_YEAR . "-{$month}-{$day}");
		$next = $date->modify('+1 day');

		return ['month' => (int)$next->format('n'), 'day' => (int)$next->format('j')];
	}

	/**
	 * Map virtual Feb 29/30 to real calendar positions.
	 *
	 * @return array{month: int, day: int}
	 */
	public static function toRealCalendar(int $month, int $day): array
	{
		if ($month === 2 && $day === 29) {
			return ['month' => 3, 'day' => 1];
		}
		if ($month === 2 && $day === 30) {
			return ['month' => 3, 'day' => 2];
		}

		return ['month' => $month, 'day' => $day];
	}

	/**
	 * Returns true for all real calendar days and virtual Feb 29, 30.
	 */
	public static function isValidDay(int $month, int $day): bool
	{
		if ($month < 1 || $month > 12 || $day < 1) {
			return false;
		}
		if ($month === 2 && $day >= 29 && $day <= 30) {
			return true;
		}

		$date = new DateTimeImmutable(self::REF_YEAR . "-{$month}-1");
		$maxDay = (int)$date->format('t');

		return $day <= $maxDay;
	}

	/**
	 * Navigate one step forward or backward, including virtual Feb dates.
	 *
	 * @return array{month: int, day: int}
	 */
	public static function getAdjacentDay(int $month, int $day, int $direction): array
	{
		if ($direction === 1) {
			return self::nextDay($month, $day);
		}

		if ($month === 2 && $day === 30) {
			return ['month' => 2, 'day' => 29];
		}
		if ($month === 2 && $day === 29) {
			return ['month' => 2, 'day' => 28];
		}
		if ($month === 3 && $day === 1) {
			return ['month' => 2, 'day' => 30];
		}
		if ($day > 1) {
			return ['month' => $month, 'day' => $day - 1];
		}
		if ($month === 1) {
			return ['month' => 12, 'day' => 31];
		}

		$prevMonth = new DateTimeImmutable(self::REF_YEAR . '-' . ($month - 1) . '-1');
		$lastDay = (int)$prevMonth->format('t');

		return ['month' => $month - 1, 'day' => $lastDay];
	}

	/**
	 * Days between today and the given month/day.
	 */
	public static function calcDaysFromToday(int $month, int $day, DateTimeImmutable $today): int
	{
		$year = (int)$today->format('Y');

		if ($month === 2 && $day > 28) {
			$feb28 = new DateTimeImmutable("{$year}-02-28");
			$base = (int)$today->diff($feb28)
				->format('%r%a');

			return $base + ($day - 28);
		}

		$target = new DateTimeImmutable("{$year}-{$month}-{$day}");

		return (int)$today->diff($target)
			->format('%r%a');
	}

	/**
	 * Generate at least $minCount display entries starting from the given date.
	 *
	 * @return list<array{month: int, day: int, daysFromToday: int}>
	 */
	public static function generateDateEntries(int $startMonth, int $startDay, int $minCount, ?\DateTimeZone $tz = null): array
	{
		$today = new DateTimeImmutable('today', $tz);
		$entries = [];
		$month = $startMonth;
		$day = $startDay;

		while (count($entries) < $minCount) {
			$diff = self::calcDaysFromToday($month, $day, $today);
			$entries[] = ['month' => $month, 'day' => $day, 'daysFromToday' => $diff];

			if ($month === 2 && $day === 28) {
				$entries[] = ['month' => 2, 'day' => 29, 'daysFromToday' => $diff + 1];
				$entries[] = ['month' => 2, 'day' => 30, 'daysFromToday' => $diff + 2];
			} else if ($month === 2 && $day === 29) {
				$entries[] = ['month' => 2, 'day' => 30, 'daysFromToday' => $diff + 1];
			}

			$next = self::nextRealDay($month, $day);
			$month = $next['month'];
			$day = $next['day'];
		}

		return $entries;
	}

	/**
	 * Advance forward by exactly $count display entries.
	 *
	 * @return array{month: int, day: int}
	 */
	public static function advanceEntries(int $startMonth, int $startDay, int $count): array
	{
		$month = $startMonth;
		$day = $startDay;
		$advanced = 0;

		while ($advanced < $count) {
			$contribution = 1;
			if ($month === 2 && $day === 28) {
				$contribution = 3;
			} else {
				if ($month === 2 && $day === 29) {
					$contribution = 2;
				}
			}

			$advanced += $contribution;
			$next = self::nextRealDay($month, $day);
			$month = $next['month'];
			$day = $next['day'];
		}

		return ['month' => $month, 'day' => $day];
	}

	/**
	 * Get a random date including virtual Feb 29/30.
	 *
	 * @return array{month: int, day: int}
	 */
	public static function getRandomDate(): array
	{
		$totalDays = array_sum(self::MONTH_DAYS);
		$n = random_int(0, $totalDays - 1);

		for ($m = 0; $m < 12; $m++) {
			if ($n < self::MONTH_DAYS[$m]) {
				return ['month' => $m + 1, 'day' => $n + 1];
			}
			$n -= self::MONTH_DAYS[$m];
		}

		return ['month' => 12, 'day' => 31];
	}
}
