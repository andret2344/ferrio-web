<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\LanguageResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
	public function __construct(
		private readonly LanguageResolver $languageResolver,
	) {}

	#[Route('/', name: 'home')]
	public function index(Request $request): Response
	{
		$locale = $this->languageResolver->resolve($request);
		$now = new \DateTimeImmutable();

		return $this->redirectToRoute('day', [
			'_locale' => $locale,
			'month' => (int)$now->format('n'),
			'day' => (int)$now->format('j'),
		]);
	}
}
