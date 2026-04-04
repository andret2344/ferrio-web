<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\TimezoneResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class TimezoneResolverTest extends TestCase
{
	private TimezoneResolver $resolver;

	protected function setUp(): void
	{
		$this->resolver = new TimezoneResolver();
	}

	#[Test]
	public function defaultsToUtcWhenNoCookie(): void
	{
		$request = Request::create('/');

		$tz = $this->resolver->resolve($request);

		self::assertSame('UTC', $tz->getName());
	}

	#[Test]
	public function usesTimezoneFromCookie(): void
	{
		$request = Request::create('/');
		$request->cookies->set('timezone', 'Europe/Warsaw');

		$tz = $this->resolver->resolve($request);

		self::assertSame('Europe/Warsaw', $tz->getName());
	}

	#[Test]
	public function fallsBackToUtcForInvalidTimezone(): void
	{
		$request = Request::create('/');
		$request->cookies->set('timezone', 'Invalid/Timezone');

		$tz = $this->resolver->resolve($request);

		self::assertSame('UTC', $tz->getName());
	}

	#[Test]
	public function handlesAmericanTimezone(): void
	{
		$request = Request::create('/');
		$request->cookies->set('timezone', 'America/New_York');

		$tz = $this->resolver->resolve($request);

		self::assertSame('America/New_York', $tz->getName());
	}
}
