<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

final class NotFoundTest extends WebTestCase
{
	#[Test]
	public function unmatchedRootPathReturns404NotCrash(): void
	{
		$client = static::createClient();
		$client->catchExceptions(true);
		$client->request('GET', '/Ferrio%20square.png');

		self::assertResponseStatusCodeSame(404);
	}

	#[Test]
	public function unmatchedLocalePrefixedPathReturns404(): void
	{
		$client = static::createClient();
		$client->catchExceptions(true);
		$client->request('GET', '/en/this-page-does-not-exist');

		self::assertResponseStatusCodeSame(404);
	}
}
