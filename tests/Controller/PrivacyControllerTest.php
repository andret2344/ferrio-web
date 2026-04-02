<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

final class PrivacyControllerTest extends WebTestCase
{
	#[Test]
	public function privacyReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/privacy');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function privacyHasCacheHeaders(): void
	{
		$client = static::createClient();
		$client->request('GET', '/privacy');

		$cacheControl = $client->getResponse()->headers->get('Cache-Control') ?? '';
		self::assertStringContainsString('public', $cacheControl);
	}

	#[Test]
	public function privacyContainsTitle(): void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/privacy');

		self::assertResponseIsSuccessful();
		self::assertSelectorExists('main');
	}
}
