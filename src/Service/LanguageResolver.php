<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

final class LanguageResolver
{
	private const array SUPPORTED_LANGUAGES = ['en', 'pl'];
	private const string DEFAULT_LANGUAGE = 'en';

	public function resolve(Request $request): string
	{
		$lang = $request->cookies->get('language', $request->getPreferredLanguage(self::SUPPORTED_LANGUAGES) ?? self::DEFAULT_LANGUAGE);

		return in_array($lang, self::SUPPORTED_LANGUAGES, true) ? $lang : self::DEFAULT_LANGUAGE;
	}
}
