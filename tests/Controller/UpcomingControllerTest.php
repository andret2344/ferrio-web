<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

final class UpcomingControllerTest extends WebTestCase
{
	#[Test]
	public function upcomingRedirectsToWeek0(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/upcoming');

		self::assertResponseRedirects('/en/upcoming/0');
	}

	#[Test]
	public function week0Returns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/upcoming/0');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function positiveWeekReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/upcoming/1');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function negativeWeekReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/upcoming/-1');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function extremeWeekIsClamped(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/upcoming/9999');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function legacyUrlRedirectsToLocalized(): void
	{
		$client = static::createClient();
		$client->request('GET', '/upcoming/0');

		self::assertResponseStatusCodeSame(301);
		self::assertStringContainsString('/upcoming/0', $client->getResponse()->headers->get('Location') ?? '');
	}
}
