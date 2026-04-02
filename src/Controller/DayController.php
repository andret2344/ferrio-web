<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\HolidayConnector;
use App\Service\LanguageResolver;
use App\Service\VirtualDate;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DayController extends AbstractController
{
	public function __construct(
		private readonly HolidayConnector $connector,
		private readonly LanguageResolver $languageResolver,
		private readonly LoggerInterface  $logger,
	) {}

	#[Route('/day/{month}/{day}', name: 'day', requirements: ['month' => '\d+', 'day' => '\d+'])]
	public function show(int $month, int $day, Request $request): Response
	{
		if (!VirtualDate::isValidDay($month, $day)) {
			return $this->redirectToRoute('home');
		}

		$lang = $this->languageResolver->resolve($request);
		$hasError = false;

		try {
			$dayResult = $this->connector->getDayResult($month, $day, $lang);
		} catch (\Throwable $e) {
			$this->logger->error('Failed to fetch holidays for day', [
				'month' => $month,
				'day' => $day,
				'lang' => $lang,
				'error' => $e->getMessage(),
			]);
			$hasError = true;
			$dayResult = null;
		}

		$prev = VirtualDate::getAdjacentDay($month, $day, -1);
		$next = VirtualDate::getAdjacentDay($month, $day, 1);
		$random = VirtualDate::getRandomDate();

		return $this->render('day/show.html.twig', [
			'month' => $month,
			'day' => $day,
			'lang' => $lang,
			'dayResult' => $dayResult,
			'hasError' => $hasError,
			'prevDay' => $prev,
			'nextDay' => $next,
			'randomDay' => $random,
		]);
	}
}
