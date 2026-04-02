<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\LanguageResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrivacyController extends AbstractController
{
	public function __construct(
		private readonly LanguageResolver $languageResolver,
	) {}

	#[Route('/privacy', name: 'privacy')]
	public function show(Request $request): Response
	{
		$response = $this->render('privacy/show.html.twig', [
			'lang' => $this->languageResolver->resolve($request),
		]);

		$response->setSharedMaxAge(86400);
		$response->headers->set('Cache-Control', 'public, max-age=3600, s-maxage=86400');

		return $response;
	}
}
