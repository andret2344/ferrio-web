<?php

declare(strict_types=1);

namespace App\Service;

final class DateFormatter
{
	private const array ENGLISH_MONTHS = [
		1 => 'January',
		2 => 'February',
		3 => 'March',
		4 => 'April',
		5 => 'May',
		6 => 'June',
		7 => 'July',
		8 => 'August',
		9 => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December',
	];

	private const array POLISH_MONTHS = [
		1 => 'stycznia',
		2 => 'lutego',
		3 => 'marca',
		4 => 'kwietnia',
		5 => 'maja',
		6 => 'czerwca',
		7 => 'lipca',
		8 => 'sierpnia',
		9 => 'września',
		10 => 'października',
		11 => 'listopada',
		12 => 'grudnia',
	];

	public static function formatMonthDay(int $day, int $month, string $lang): string
	{
		if ($month === 2 && $day > 28) {
			if ($lang === 'pl') {
				return "{$day} lutego";
			}

			return "{$day}" . self::getDaySuffix($day) . ' of February';
		}

		if ($lang === 'pl') {
			return "{$day} " . self::POLISH_MONTHS[$month];
		}

		return "{$day}" . self::getDaySuffix($day) . ' of ' . self::ENGLISH_MONTHS[$month];
	}

	private static function getDaySuffix(int $day): string
	{
		if ($day > 3 && $day < 21) {
			return 'th';
		}

		return match ($day % 10) {
			1 => 'st',
			2 => 'nd',
			3 => 'rd',
			default => 'th',
		};
	}
}
