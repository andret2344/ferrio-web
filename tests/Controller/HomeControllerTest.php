<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\Test;

final class HomeControllerTest extends WebTestCase
{
	#[Test]
	public function homeRedirectsToDayRoute(): void
	{
		$client = static::createClient();
		$client->request('GET', '/');

		self::assertResponseRedirects();
		self::assertStringContainsString('/day/', $client->getResponse()->headers->get('Location') ?? '');
	}
}
