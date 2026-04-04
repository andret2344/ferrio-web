<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\VirtualDate;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VirtualDateTest extends TestCase
{
	#[Test]
	#[DataProvider('nextDayProvider')]
	public function nextDay(int $month, int $day, int $expectedMonth, int $expectedDay): void
	{
		$result = VirtualDate::nextDay($month, $day);

		self::assertSame($expectedMonth, $result['month']);
		self::assertSame($expectedDay, $result['day']);
	}

	public static function nextDayProvider(): iterable
	{
		yield 'Jan 1 → Jan 2' => [1, 1, 1, 2];
		yield 'Jan 31 → Feb 1' => [1, 31, 2, 1];
		yield 'Feb 27 → Feb 28' => [2, 27, 2, 28];
		yield 'Feb 28 → virtual Feb 29' => [2, 28, 2, 29];
		yield 'virtual Feb 29 → virtual Feb 30' => [2, 29, 2, 30];
		yield 'virtual Feb 30 → Mar 1' => [2, 30, 3, 1];
		yield 'Dec 31 → Jan 1' => [12, 31, 1, 1];
		yield 'Jun 30 → Jul 1' => [6, 30, 7, 1];
	}

	#[Test]
	#[DataProvider('nextRealDayProvider')]
	public function nextRealDay(int $month, int $day, int $expectedMonth, int $expectedDay): void
	{
		$result = VirtualDate::nextRealDay($month, $day);

		self::assertSame($expectedMonth, $result['month']);
		self::assertSame($expectedDay, $result['day']);
	}

	public static function nextRealDayProvider(): iterable
	{
		yield 'Jan 15 → Jan 16' => [1, 15, 1, 16];
		yield 'Feb 28 → Mar 1 (skips virtual)' => [2, 28, 3, 1];
		yield 'Feb 29 → Mar 1 (skips virtual)' => [2, 29, 3, 1];
		yield 'Feb 30 → Mar 1 (skips virtual)' => [2, 30, 3, 1];
		yield 'Mar 1 → Mar 2' => [3, 1, 3, 2];
	}

	#[Test]
	#[DataProvider('toRealCalendarProvider')]
	public function toRealCalendar(int $month, int $day, int $expectedMonth, int $expectedDay): void
	{
		$result = VirtualDate::toRealCalendar($month, $day);

		self::assertSame($expectedMonth, $result['month']);
		self::assertSame($expectedDay, $result['day']);
	}

	public static function toRealCalendarProvider(): iterable
	{
		yield 'Jan 15 stays' => [1, 15, 1, 15];
		yield 'Feb 28 stays' => [2, 28, 2, 28];
		yield 'virtual Feb 29 → Mar 1' => [2, 29, 3, 1];
		yield 'virtual Feb 30 → Mar 2' => [2, 30, 3, 2];
		yield 'Mar 1 stays' => [3, 1, 3, 1];
	}

	#[Test]
	#[DataProvider('isValidDayProvider')]
	public function isValidDay(int $month, int $day, bool $expected): void
	{
		self::assertSame($expected, VirtualDate::isValidDay($month, $day));
	}

	public static function isValidDayProvider(): iterable
	{
		yield 'Jan 1 valid' => [1, 1, true];
		yield 'Jan 31 valid' => [1, 31, true];
		yield 'Feb 28 valid' => [2, 28, true];
		yield 'Feb 29 valid (virtual)' => [2, 29, true];
		yield 'Feb 30 valid (virtual)' => [2, 30, true];
		yield 'Feb 31 invalid' => [2, 31, false];
		yield 'Apr 30 valid' => [4, 30, true];
		yield 'Apr 31 invalid' => [4, 31, false];
		yield 'Dec 31 valid' => [12, 31, true];
		yield 'month 0 invalid' => [0, 1, false];
		yield 'month 13 invalid' => [13, 1, false];
		yield 'day 0 invalid' => [1, 0, false];
	}

	#[Test]
	#[DataProvider('adjacentDayForwardProvider')]
	public function adjacentDayForward(int $month, int $day, int $expectedMonth, int $expectedDay): void
	{
		$result = VirtualDate::getAdjacentDay($month, $day, 1);

		self::assertSame($expectedMonth, $result['month']);
		self::assertSame($expectedDay, $result['day']);
	}

	public static function adjacentDayForwardProvider(): iterable
	{
		yield 'Jan 1 → Jan 2' => [1, 1, 1, 2];
		yield 'Feb 28 → Feb 29' => [2, 28, 2, 29];
		yield 'Feb 29 → Feb 30' => [2, 29, 2, 30];
		yield 'Feb 30 → Mar 1' => [2, 30, 3, 1];
	}

	#[Test]
	#[DataProvider('adjacentDayBackwardProvider')]
	public function adjacentDayBackward(int $month, int $day, int $expectedMonth, int $expectedDay): void
	{
		$result = VirtualDate::getAdjacentDay($month, $day, -1);

		self::assertSame($expectedMonth, $result['month']);
		self::assertSame($expectedDay, $result['day']);
	}

	public static function adjacentDayBackwardProvider(): iterable
	{
		yield 'Jan 2 → Jan 1' => [1, 2, 1, 1];
		yield 'Jan 1 → Dec 31 (wrap)' => [1, 1, 12, 31];
		yield 'Mar 1 → Feb 30 (virtual)' => [3, 1, 2, 30];
		yield 'Feb 30 → Feb 29' => [2, 30, 2, 29];
		yield 'Feb 29 → Feb 28' => [2, 29, 2, 28];
		yield 'Apr 1 → Mar 31' => [4, 1, 3, 31];
	}

	#[Test]
	public function calcDaysFromTodayForRegularDate(): void
	{
		$today = new DateTimeImmutable('2026-04-02');
		$result = VirtualDate::calcDaysFromToday(4, 5, $today);

		self::assertSame(3, $result);
	}

	#[Test]
	public function calcDaysFromTodayForPastDate(): void
	{
		$today = new DateTimeImmutable('2026-04-05');
		$result = VirtualDate::calcDaysFromToday(4, 2, $today);

		self::assertSame(-3, $result);
	}

	#[Test]
	public function calcDaysFromTodayForVirtualFeb29(): void
	{
		$today = new DateTimeImmutable('2026-02-26');
		$result = VirtualDate::calcDaysFromToday(2, 29, $today);

		// Feb 28 is 2 days away, +1 for virtual Feb 29
		self::assertSame(3, $result);
	}

	#[Test]
	public function calcDaysFromTodayForVirtualFeb30(): void
	{
		$today = new DateTimeImmutable('2026-02-26');
		$result = VirtualDate::calcDaysFromToday(2, 30, $today);

		// Feb 28 is 2 days away, +2 for virtual Feb 30
		self::assertSame(4, $result);
	}

	#[Test]
	public function generateDateEntriesReturnsAtLeastMinCount(): void
	{
		$entries = VirtualDate::generateDateEntries(6, 1, 7);

		self::assertGreaterThanOrEqual(7, count($entries));
		self::assertSame(6, $entries[0]['month']);
		self::assertSame(1, $entries[0]['day']);
	}

	#[Test]
	public function generateDateEntriesIncludesVirtualFebDays(): void
	{
		$entries = VirtualDate::generateDateEntries(2, 27, 7);

		$dates = array_map(fn(array $e) => "{$e['month']}/{$e['day']}", $entries);

		self::assertContains('2/28', $dates);
		self::assertContains('2/29', $dates);
		self::assertContains('2/30', $dates);
		self::assertContains('3/1', $dates);
	}

	#[Test]
	public function generateDateEntriesCrossingFebBoundaryHasCorrectOrder(): void
	{
		$entries = VirtualDate::generateDateEntries(2, 26, 7);

		$dates = array_map(fn(array $e) => "{$e['month']}/{$e['day']}", $entries);

		// Should produce: 2/26, 2/27, 2/28, 2/29, 2/30, 3/1, 3/2, (possibly more)
		self::assertSame('2/26', $dates[0]);
		self::assertSame('2/27', $dates[1]);
		self::assertSame('2/28', $dates[2]);
		self::assertSame('2/29', $dates[3]);
		self::assertSame('2/30', $dates[4]);
		self::assertSame('3/1', $dates[5]);
		self::assertSame('3/2', $dates[6]);
		self::assertGreaterThanOrEqual(7, count($entries));
	}

	#[Test]
	public function generateDateEntriesStartingAtFeb28ProducesVirtualDays(): void
	{
		$entries = VirtualDate::generateDateEntries(2, 28, 7);

		$dates = array_map(fn(array $e) => "{$e['month']}/{$e['day']}", $entries);

		// Feb 28 contributes 3 entries (28, 29, 30), then real days continue
		self::assertSame('2/28', $dates[0]);
		self::assertSame('2/29', $dates[1]);
		self::assertSame('2/30', $dates[2]);
		self::assertSame('3/1', $dates[3]);
		self::assertGreaterThanOrEqual(7, count($entries));
	}

	#[Test]
	public function generateDateEntriesDaysFromTodayIncrementsForVirtualDays(): void
	{
		$entries = VirtualDate::generateDateEntries(2, 28, 7);

		// Virtual Feb 29 should be exactly 1 more than Feb 28
		$feb28Diff = $entries[0]['daysFromToday'];
		self::assertSame($feb28Diff + 1, $entries[1]['daysFromToday']); // Feb 29
		self::assertSame($feb28Diff + 2, $entries[2]['daysFromToday']); // Feb 30
	}

	#[Test]
	public function advanceEntriesSkipsCorrectly(): void
	{
		// Starting from Jun 1, advance 7 entries
		$result = VirtualDate::advanceEntries(6, 1, 7);

		self::assertSame(6, $result['month']);
		self::assertSame(8, $result['day']);
	}

	#[Test]
	public function advanceEntriesCountsVirtualFebDaysCorrectly(): void
	{
		// Feb 28 contributes 3 entries (28, 29, 30), so advancing 3 from Feb 28
		$result = VirtualDate::advanceEntries(2, 28, 3);

		self::assertSame(3, $result['month']);
		self::assertSame(1, $result['day']);
	}

	#[Test]
	public function getRandomDateReturnsValidDate(): void
	{
		$result = VirtualDate::getRandomDate();

		self::assertArrayHasKey('month', $result);
		self::assertArrayHasKey('day', $result);
		self::assertTrue(VirtualDate::isValidDay($result['month'], $result['day']));
	}

	#[Test]
	public function forwardThenBackwardReturnsToOriginal(): void
	{
		$original = ['month' => 5, 'day' => 15];
		$forward = VirtualDate::getAdjacentDay($original['month'], $original['day'], 1);
		$back = VirtualDate::getAdjacentDay($forward['month'], $forward['day'], -1);

		self::assertSame($original, $back);
	}

	#[Test]
	public function forwardThenBackwardThroughVirtualFeb(): void
	{
		$start = ['month' => 2, 'day' => 28];
		$step1 = VirtualDate::getAdjacentDay(2, 28, 1);  // Feb 29
		$step2 = VirtualDate::getAdjacentDay($step1['month'], $step1['day'], 1);  // Feb 30
		$step3 = VirtualDate::getAdjacentDay($step2['month'], $step2['day'], 1);  // Mar 1

		self::assertSame(['month' => 2, 'day' => 29], $step1);
		self::assertSame(['month' => 2, 'day' => 30], $step2);
		self::assertSame(['month' => 3, 'day' => 1], $step3);

		// Walk back
		$back1 = VirtualDate::getAdjacentDay(3, 1, -1);  // Feb 30
		$back2 = VirtualDate::getAdjacentDay($back1['month'], $back1['day'], -1);  // Feb 29
		$back3 = VirtualDate::getAdjacentDay($back2['month'], $back2['day'], -1);  // Feb 28

		self::assertSame(['month' => 2, 'day' => 30], $back1);
		self::assertSame(['month' => 2, 'day' => 29], $back2);
		self::assertSame($start, $back3);
	}
}
