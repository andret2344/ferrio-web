<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Country;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CountryTest extends TestCase
{
	#[Test]
	public function knownCountryResolvesNameAndFlag(): void
	{
		$country = new Country('US');

		self::assertSame('United States', $country->name);
		self::assertSame('US', $country->code);
		self::assertSame("\u{1F1FA}\u{1F1F8}", $country->flag);
	}

	#[Test]
	public function polishCountryResolvesCorrectly(): void
	{
		$country = new Country('PL');

		self::assertSame('Poland', $country->name);
		self::assertSame("\u{1F1F5}\u{1F1F1}", $country->flag);
	}

	#[Test]
	public function unknownCountryFallsBackToCode(): void
	{
		$country = new Country('XX');

		self::assertSame('XX', $country->name);
		self::assertNotEmpty($country->flag);
	}

	#[Test]
	public function lowercaseCodeIsNormalized(): void
	{
		$country = new Country('gb');

		self::assertSame('United Kingdom', $country->name);
		self::assertSame('gb', $country->code);
	}

	#[Test]
	public function flagEmojiHasCorrectLength(): void
	{
		$country = new Country('JP');

		self::assertSame('Japan', $country->name);
		// Flag emoji is 2 regional indicator symbols
		self::assertSame(2, mb_strlen($country->flag));
	}
}
