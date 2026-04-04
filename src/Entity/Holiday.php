<?php

declare(strict_types=1);

namespace App\Entity;

final readonly class Holiday
{
	public function __construct(
		public string   $name,
		public ?string  $description,
		public ?Country $country,
		public ?string  $url,
		public bool     $usual,
	) {}

	/**
	 * @param array<string, mixed> $data
	 */
	public static function fromApiData(array $data): self
	{
		$country = !empty($data['country']) ? new Country($data['country']) : null;
		$url = $data['url'] ?? null;

		if ($url !== null && (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true))) {
			$url = null;
		}

		return new self(
			name: $data['name'] ?? '',
			description: $data['description'] ?? null,
			country: $country,
			url: $url,
			usual: (bool)($data['usual'] ?? false),
		);
	}
}
