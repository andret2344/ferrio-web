<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Service\FerrioApiClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

final class UpcomingControllerErrorTest extends WebTestCase
{
	#[Test]
	public function apiFailureShowsErrorPage(): void
	{
		$client = static::createClient();

		$mock = $this->createMock(FerrioApiClient::class);
		$mock->method('fetchHolidaysByDate')
			->willThrowException(new \RuntimeException('API unavailable'));

		$client->getContainer()->set(FerrioApiClient::class, $mock);
		$crawler = $client->request('GET', '/en/upcoming/0');

		self::assertResponseIsSuccessful();
		self::assertSelectorExists('.text-red-600, .text-red-900');
	}

	#[Test]
	public function apiFailureStillShowsNavigation(): void
	{
		$client = static::createClient();

		$mock = $this->createMock(FerrioApiClient::class);
		$mock->method('fetchHolidaysByDate')
			->willThrowException(new \RuntimeException('timeout'));

		$client->getContainer()->set(FerrioApiClient::class, $mock);
		$client->request('GET', '/en/upcoming/0');

		self::assertResponseIsSuccessful();
		self::assertSelectorExists('nav');
	}

	#[Test]
	public function apiFailureReturnsCacheHeaders(): void
	{
		$client = static::createClient();

		$mock = $this->createMock(FerrioApiClient::class);
		$mock->method('fetchHolidaysByDate')
			->willThrowException(new \RuntimeException('error'));

		$client->getContainer()->set(FerrioApiClient::class, $mock);
		$client->request('GET', '/en/upcoming/0');

		$cacheControl = $client->getResponse()->headers->get('Cache-Control');
		self::assertStringContainsString('public', $cacheControl);
	}
}
