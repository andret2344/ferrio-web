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
		$client->request('GET', '/en/day/4/2');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function invalidDateRedirectsToHome(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/day/13/1');

		self::assertResponseRedirects('/');
	}

	#[Test]
	public function invalidDayRedirectsToHome(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/day/4/31');

		self::assertResponseRedirects('/');
	}

	#[Test]
	public function virtualFeb29Returns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/day/2/29');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function virtualFeb30Returns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/day/2/30');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function responseContainsHolidayContent(): void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/en/day/1/1');

		self::assertResponseIsSuccessful();
		self::assertSelectorExists('main');
	}

	#[Test]
	public function responseContainsHreflangAlternates(): void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/en/day/1/1');

		self::assertResponseIsSuccessful();

		$hreflangEn = $crawler->filter('link[hreflang="en"]');
		$hreflangPl = $crawler->filter('link[hreflang="pl"]');
		$hreflangDefault = $crawler->filter('link[hreflang="x-default"]');

		self::assertCount(1, $hreflangEn);
		self::assertCount(1, $hreflangPl);
		self::assertCount(1, $hreflangDefault);
		self::assertStringContainsString('/en/day/1/1', $hreflangEn->attr('href'));
		self::assertStringContainsString('/pl/day/1/1', $hreflangPl->attr('href'));
	}

	#[Test]
	public function responseContainsCanonicalUrl(): void
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/en/day/1/1');

		self::assertResponseIsSuccessful();

		$canonical = $crawler->filter('link[rel="canonical"]');
		self::assertCount(1, $canonical);
		self::assertStringContainsString('/en/day/1/1', $canonical->attr('href'));
	}

	#[Test]
	public function responseContainsSecurityHeaders(): void
	{
		$client = static::createClient();
		$client->request('GET', '/en/day/1/1');

		self::assertResponseIsSuccessful();
		self::assertResponseHeaderSame('X-Frame-Options', 'DENY');
		self::assertResponseHeaderSame('X-Content-Type-Options', 'nosniff');
		self::assertResponseHeaderSame('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
	}

	#[Test]
	public function polishLocaleReturns200(): void
	{
		$client = static::createClient();
		$client->request('GET', '/pl/day/1/1');

		self::assertResponseIsSuccessful();
	}

	#[Test]
	public function legacyUrlRedirectsToLocalized(): void
	{
		$client = static::createClient();
		$client->request('GET', '/day/4/2');

		self::assertResponseStatusCodeSame(301);
		self::assertStringContainsString('/day/4/2', $client->getResponse()->headers->get('Location') ?? '');
	}
}
