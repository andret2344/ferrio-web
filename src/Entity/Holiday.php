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

		return new self(
			name: $data['name'] ?? '',
			description: $data['description'] ?? null,
			country: $country,
			url: $data['url'] ?? null,
			usual: (bool)($data['usual'] ?? false),
		);
	}
}
