<?php

declare(strict_types=1);

namespace App\Entity;

final readonly class DayResult
{
	/**
	 * @param list<Holiday> $holidays
	 */
	public function __construct(
		public int    $month,
		public int    $day,
		public string $formattedDate,
		public array  $holidays,
		public ?int   $daysFromToday = null,
	) {}
}
