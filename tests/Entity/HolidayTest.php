<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Holiday;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HolidayTest extends TestCase
{
	#[Test]
	public function fromApiDataAcceptsHttpsUrl(): void
	{
		$holiday = Holiday::fromApiData([
			'name' => 'Test',
			'url' => 'https://example.com',
		]);

		self::assertSame('https://example.com', $holiday->url);
	}

	#[Test]
	public function fromApiDataAcceptsHttpUrl(): void
	{
		$holiday = Holiday::fromApiData([
			'name' => 'Test',
			'url' => 'http://example.com',
		]);

		self::assertSame('http://example.com', $holiday->url);
	}

	#[Test]
	#[DataProvider('maliciousUrlProvider')]
	public function fromApiDataRejectsMaliciousUrl(string $url): void
	{
		$holiday = Holiday::fromApiData([
			'name' => 'Test',
			'url' => $url,
		]);

		self::assertNull($holiday->url);
	}

	public static function maliciousUrlProvider(): iterable
	{
		yield 'javascript protocol' => ['javascript:alert(1)'];
		yield 'data protocol' => ['data:text/html,<script>alert(1)</script>'];
		yield 'vbscript protocol' => ['vbscript:msgbox'];
		yield 'relative path' => ['/some/path'];
		yield 'bare domain' => ['example.com'];
		yield 'scheme only' => ['https://'];
		yield 'scheme with no host' => ['http://'];
	}

	#[Test]
	public function fromApiDataHandlesNullUrl(): void
	{
		$holiday = Holiday::fromApiData([
			'name' => 'Test',
		]);

		self::assertNull($holiday->url);
	}

	#[Test]
	public function fromApiDataParsesAllFields(): void
	{
		$holiday = Holiday::fromApiData([
			'name' => 'World Pizza Day',
			'description' => 'A day for pizza lovers',
			'country' => 'IT',
			'url' => 'https://pizza.example.com',
			'usual' => true,
		]);

		self::assertSame('World Pizza Day', $holiday->name);
		self::assertSame('A day for pizza lovers', $holiday->description);
		self::assertNotNull($holiday->country);
		self::assertSame('https://pizza.example.com', $holiday->url);
		self::assertTrue($holiday->usual);
	}
}
