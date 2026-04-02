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
		$client->request('GET', '/upcoming');

		self::assertResponseRedirects('/upcoming/0');
	}

	#[Test]
	public function week0Returns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/upcoming/0');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function positiveWeekReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/upcoming/1');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function negativeWeekReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/upcoming/-1');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function extremeWeekIsClamped(): void
	{
		$client = static::createClient();
		$client->request('GET', '/upcoming/9999');

		self::assertResponseIsSuccessful();
	}
}
