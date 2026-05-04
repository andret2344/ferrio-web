<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

final class AppsControllerTest extends WebTestCase
{
	#[Test]
	public function appsReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/apps');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function appsHasCacheHeaders(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/apps');

		$cacheControl = $client->getResponse()->headers->get('Cache-Control') ?? '';
		self::assertStringContainsString('public', $cacheControl);
	}

	#[Test]
	public function appsContainsMain(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/apps');

		self::assertResponseIsSuccessful();
		self::assertSelectorExists('main');
	}

	#[Test]
	public function polishAppsReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/pl/apps');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function legacyUrlRedirectsToLocalized(): void
	{
		$client = static::createClient();
		$client->request('GET', '/apps');

		self::assertResponseStatusCodeSame(301);
		self::assertStringContainsString('/apps', $client->getResponse()->headers->get('Location') ?? '');
	}
}
