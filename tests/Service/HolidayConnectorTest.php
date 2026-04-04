<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\DayResult;
use App\Entity\Holiday;
use App\Service\FerrioApiClient;
use App\Service\HolidayConnector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class HolidayConnectorTest extends TestCase
{
	private FerrioApiClient&MockObject $apiClient;
	private HolidayConnector $connector;

	protected function setUp(): void
	{
		$this->apiClient = $this->createMock(FerrioApiClient::class);
		$this->connector = new HolidayConnector($this->apiClient, new NullLogger());
	}

	#[Test]
	public function getHolidaysForDateReturnsHolidayObjects(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([
			['name' => 'Test Holiday', 'description' => 'A test', 'country' => 'US', 'url' => null, 'usual' => false],
		]);

		$holidays = $this->connector->getHolidaysForDate(1, 1, 'en');

		self::assertCount(1, $holidays);
		self::assertInstanceOf(Holiday::class, $holidays[0]);
		self::assertSame('Test Holiday', $holidays[0]->name);
	}

	#[Test]
	public function getHolidaysForDateReturnsEmptyArrayWhenNoHolidays(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([]);

		$holidays = $this->connector->getHolidaysForDate(6, 15, 'en');

		self::assertSame([], $holidays);
	}

	#[Test]
	public function getDayResultReturnsDayResultWithFormattedDate(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([
			['name' => 'New Year', 'description' => null, 'country' => null, 'url' => null, 'usual' => true],
		]);

		$result = $this->connector->getDayResult(1, 1, 'en');

		self::assertInstanceOf(DayResult::class, $result);
		self::assertSame(1, $result->month);
		self::assertSame(1, $result->day);
		self::assertStringContainsString('January', $result->formattedDate);
		self::assertCount(1, $result->holidays);
		self::assertNull($result->daysFromToday);
	}

	#[Test]
	public function getDayResultPassesDaysFromToday(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([]);

		$result = $this->connector->getDayResult(3, 15, 'en', 5);

		self::assertSame(5, $result->daysFromToday);
	}

	#[Test]
	public function getDayResultPolishFormatting(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([]);

		$result = $this->connector->getDayResult(3, 15, 'pl');

		self::assertStringContainsString('marca', $result->formattedDate);
	}

	#[Test]
	public function getDayResultSafeReturnsNullOnException(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')
			->willThrowException(new \RuntimeException('API down'));

		$result = $this->connector->getDayResultSafe(1, 1, 'en');

		self::assertNull($result);
	}

	#[Test]
	public function getDayResultSafeReturnsDayResultOnSuccess(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([]);

		$result = $this->connector->getDayResultSafe(1, 1, 'en');

		self::assertInstanceOf(DayResult::class, $result);
	}

	#[Test]
	public function getUpcomingDaysReturnsSevenOrMoreDays(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([]);

		$days = $this->connector->getUpcomingDays(0, 'en');

		self::assertGreaterThanOrEqual(7, count($days));
		foreach ($days as $day) {
			self::assertInstanceOf(DayResult::class, $day);
		}
	}

	#[Test]
	public function getUpcomingDaysClampsWeekOffset(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([]);

		$days = $this->connector->getUpcomingDays(9999, 'en');

		self::assertGreaterThanOrEqual(7, count($days));
	}

	#[Test]
	public function getUpcomingDaysNegativeOffset(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([]);

		$days = $this->connector->getUpcomingDays(-1, 'en');

		self::assertGreaterThanOrEqual(7, count($days));
	}

	#[Test]
	public function getUpcomingDaysSafeReturnsNullOnException(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')
			->willThrowException(new \RuntimeException('API down'));

		$result = $this->connector->getUpcomingDaysSafe(0, 'en');

		self::assertNull($result);
	}

	#[Test]
	public function getUpcomingDaysRespectsTimezone(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([]);

		$tz = new \DateTimeZone('America/New_York');
		$days = $this->connector->getUpcomingDays(0, 'en', $tz);

		self::assertGreaterThanOrEqual(7, count($days));
	}

	#[Test]
	public function getHolidaysForDateMapsMultipleHolidays(): void
	{
		$this->apiClient->method('fetchHolidaysByDate')->willReturn([
			['name' => 'Holiday A', 'description' => 'Desc A', 'country' => 'PL', 'url' => 'https://example.com', 'usual' => false],
			['name' => 'Holiday B', 'description' => null, 'country' => null, 'url' => null, 'usual' => true],
		]);

		$holidays = $this->connector->getHolidaysForDate(12, 25, 'en');

		self::assertCount(2, $holidays);
		self::assertSame('Holiday A', $holidays[0]->name);
		self::assertSame('PL', $holidays[0]->country->code);
		self::assertSame('Holiday B', $holidays[1]->name);
		self::assertNull($holidays[1]->country);
		self::assertTrue($holidays[1]->usual);
	}
}
