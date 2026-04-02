<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\HolidayConnector;
use App\Service\LanguageResolver;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UpcomingController extends AbstractController
{
	public function __construct(
		private readonly HolidayConnector $connector,
		private readonly LanguageResolver $languageResolver,
		private readonly LoggerInterface  $logger,
	) {}

	#[Route('/upcoming', name: 'upcoming_redirect')]
	public function redirectToDefault(): Response
	{
		return $this->redirectToRoute('upcoming', ['week' => 0]);
	}

	#[Route('/upcoming/{week}', name: 'upcoming', requirements: ['week' => '-?\d+'])]
	public function show(int $week, Request $request): Response
	{
		$lang = $this->languageResolver->resolve($request);

		try {
			$days = $this->connector->getUpcomingDays($week, $lang);
			$hasError = false;
		} catch (\Throwable $e) {
			$this->logger->error('Failed to fetch upcoming holidays', [
				'week' => $week,
				'lang' => $lang,
				'error' => $e->getMessage(),
			]);
			$days = [];
			$hasError = true;
		}

		return $this->render('upcoming/show.html.twig', [
			'weekOffset' => $week,
			'days' => $days,
			'hasError' => $hasError,
			'lang' => $lang,
		]);
	}
}
