<?php

declare(strict_types=1);

namespace App\Entity;

final readonly class Country
{
	public string $flag;
	public string $name;

	public function __construct(
		public string $code,
	)
	{
		$this->flag = self::resolveFlag($code);
		$this->name = self::resolveName($code);
	}

	private const array COUNTRY_NAMES = [
		'AE' => 'United Arab Emirates',
		'AR' => 'Argentina',
		'AT' => 'Austria',
		'AU' => 'Australia',
		'BE' => 'Belgium',
		'BR' => 'Brazil',
		'CA' => 'Canada',
		'CH' => 'Switzerland',
		'CL' => 'Chile',
		'CN' => 'China',
		'CO' => 'Colombia',
		'CZ' => 'Czech Republic',
		'DE' => 'Germany',
		'DK' => 'Denmark',
		'DO' => 'Dominican Republic',
		'EG' => 'Egypt',
		'ES' => 'Spain',
		'FI' => 'Finland',
		'FR' => 'France',
		'GB' => 'United Kingdom',
		'GR' => 'Greece',
		'HU' => 'Hungary',
		'ID' => 'Indonesia',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IN' => 'India',
		'IT' => 'Italy',
		'JP' => 'Japan',
		'KE' => 'Kenya',
		'KR' => 'South Korea',
		'MX' => 'Mexico',
		'MY' => 'Malaysia',
		'NG' => 'Nigeria',
		'NL' => 'Netherlands',
		'NO' => 'Norway',
		'NZ' => 'New Zealand',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'SA' => 'Saudi Arabia',
		'SE' => 'Sweden',
		'SG' => 'Singapore',
		'TH' => 'Thailand',
		'TR' => 'Turkey',
		'UA' => 'Ukraine',
		'US' => 'United States',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'ZA' => 'South Africa',
	];

	private static function resolveFlag(string $countryCode): string
	{
		$code = strtoupper($countryCode);
		$flag = '';

		for ($i = 0; $i < strlen($code); $i++) {
			$flag .= mb_chr(127397 + ord($code[$i]));
		}

		return $flag;
	}

	private static function resolveName(string $countryCode): string
	{
		$code = strtoupper($countryCode);

		return self::COUNTRY_NAMES[$code] ?? $code;
	}
}
