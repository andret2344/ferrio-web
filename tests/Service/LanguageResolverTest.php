<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\LanguageResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class LanguageResolverTest extends TestCase
{
	private LanguageResolver $resolver;

	protected function setUp(): void
	{
		$this->resolver = new LanguageResolver();
	}

	#[Test]
	public function defaultsToEnglish(): void
	{
		$request = Request::create('/');

		self::assertSame('en', $this->resolver->resolve($request));
	}

	#[Test]
	public function resolvesFromCookie(): void
	{
		$request = Request::create('/');
		$request->cookies->set('language', 'pl');

		self::assertSame('pl', $this->resolver->resolve($request));
	}

	#[Test]
	public function ignoresUnsupportedCookieValue(): void
	{
		$request = Request::create('/');
		$request->cookies->set('language', 'de');

		self::assertSame('en', $this->resolver->resolve($request));
	}

	#[Test]
	public function resolvesFromAcceptLanguageHeader(): void
	{
		$request = Request::create('/');
		$request->headers->set('Accept-Language', 'pl,en;q=0.5');

		self::assertSame('pl', $this->resolver->resolve($request));
	}

	#[Test]
	public function cookieTakesPrecedenceOverHeader(): void
	{
		$request = Request::create('/');
		$request->cookies->set('language', 'en');
		$request->headers->set('Accept-Language', 'pl');

		self::assertSame('en', $this->resolver->resolve($request));
	}
}
