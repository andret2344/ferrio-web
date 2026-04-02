<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

final class DayControllerTest extends WebTestCase
{
	#[Test]
	public function validDateReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/day/4/2');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function invalidDateRedirectsToHome(): void
	{
		$client = static::createClient();
		$client->request('GET', '/day/13/1');

		self::assertResponseRedirects('/');
	}

	#[Test]
	public function invalidDayRedirectsToHome(): void
	{
		$client = static::createClient();
		$client->request('GET', '/day/4/31');

		self::assertResponseRedirects('/');
	}

	#[Test]
	public function virtualFeb29Returns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/day/2/29');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function virtualFeb30Returns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/day/2/30');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function responseContainsHolidayContent(): void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/day/1/1');

		self::assertResponseIsSuccessful();
		self::assertSelectorExists('main');
	}
}
