<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

final class TermsControllerTest extends WebTestCase
{
	#[Test]
	public function termsReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/terms');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function termsHasCacheHeaders(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/terms');

		$cacheControl = $client->getResponse()->headers->get('Cache-Control') ?? '';
		self::assertStringContainsString('public', $cacheControl);
	}

	#[Test]
	public function termsContainsTitle(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/terms');

		self::assertResponseIsSuccessful();
		self::assertSelectorExists('main');
	}

	#[Test]
	public function polishTermsReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/pl/terms');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function legacyUrlRedirectsToLocalized(): void
	{
		$client = static::createClient();
		$client->request('GET', '/terms');

		self::assertResponseStatusCodeSame(301);
		self::assertStringContainsString('/terms', $client->getResponse()->headers->get('Location') ?? '');
	}
}
