<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

final class TimezoneResolver
{
	private const string DEFAULT_TIMEZONE = 'UTC';

	public function resolve(Request $request): \DateTimeZone
	{
		$tz = $request->cookies->get('timezone', self::DEFAULT_TIMEZONE);

		try {
			return new \DateTimeZone($tz);
		} catch (\Exception) {
			return new \DateTimeZone(self::DEFAULT_TIMEZONE);
		}
	}
}
