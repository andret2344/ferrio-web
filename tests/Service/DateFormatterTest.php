<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\DateFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DateFormatterTest extends TestCase
{
	#[Test]
	#[DataProvider('englishDatesProvider')]
	public function formatMonthDayEnglish(int $day, int $month, string $expected): void
	{
		self::assertSame($expected, DateFormatter::formatMonthDay($day, $month, 'en'));
	}

	public static function englishDatesProvider(): iterable
	{
		yield '1st of January' => [1, 1, '1st of January'];
		yield '2nd of March' => [2, 3, '2nd of March'];
		yield '3rd of April' => [3, 4, '3rd of April'];
		yield '4th of May' => [4, 5, '4th of May'];
		yield '11th of June' => [11, 6, '11th of June'];
		yield '12th of July' => [12, 7, '12th of July'];
		yield '13th of August' => [13, 8, '13th of August'];
		yield '21st of September' => [21, 9, '21st of September'];
		yield '22nd of October' => [22, 10, '22nd of October'];
		yield '23rd of November' => [23, 11, '23rd of November'];
		yield '31st of December' => [31, 12, '31st of December'];
	}

	#[Test]
	#[DataProvider('polishDatesProvider')]
	public function formatMonthDayPolish(int $day, int $month, string $expected): void
	{
		self::assertSame($expected, DateFormatter::formatMonthDay($day, $month, 'pl'));
	}

	public static function polishDatesProvider(): iterable
	{
		yield '1 stycznia' => [1, 1, '1 stycznia'];
		yield '15 marca' => [15, 3, '15 marca'];
		yield '30 czerwca' => [30, 6, '30 czerwca'];
		yield '31 grudnia' => [31, 12, '31 grudnia'];
	}

	#[Test]
	public function virtualFeb29English(): void
	{
		self::assertSame('29th of February', DateFormatter::formatMonthDay(29, 2, 'en'));
	}

	#[Test]
	public function virtualFeb30English(): void
	{
		self::assertSame('30th of February', DateFormatter::formatMonthDay(30, 2, 'en'));
	}

	#[Test]
	public function virtualFeb29Polish(): void
	{
		self::assertSame('29 lutego', DateFormatter::formatMonthDay(29, 2, 'pl'));
	}

	#[Test]
	public function virtualFeb30Polish(): void
	{
		self::assertSame('30 lutego', DateFormatter::formatMonthDay(30, 2, 'pl'));
	}
}
