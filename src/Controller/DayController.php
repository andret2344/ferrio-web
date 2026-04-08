<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\HolidayConnector;
use App\Service\VirtualDate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DayController extends AbstractController
{
	public function __construct(
		private readonly HolidayConnector $connector,
	) {}

	#[Route('/{_locale}/day/{month}/{day}', name: 'day', requirements: ['_locale' => 'en|pl', 'month' => '\d+', 'day' => '\d+'])]
	public function show(int $month, int $day, Request $request): Response
	{
		if (!VirtualDate::isValidDay($month, $day)) {
			return $this->redirectToRoute('home');
		}

		$lang = $request->getLocale();
		$dayResult = $this->connector->getDayResultSafe($month, $day, $lang);

		$response = $this->render('day/show.html.twig', [
			'month' => $month,
			'day' => $day,
			'lang' => $lang,
			'dayResult' => $dayResult,
			'hasError' => $dayResult === null,
			'prevDay' => VirtualDate::getAdjacentDay($month, $day, -1),
			'nextDay' => VirtualDate::getAdjacentDay($month, $day, 1),
			'randomDay' => VirtualDate::getRandomDate(),
		]);

		$response->setSharedMaxAge(3600);
		$response->headers->set('Cache-Control', 'public, max-age=900, s-maxage=3600, stale-while-revalidate=86400');
		$response->setEtag(md5($response->getContent()));

		return $response;
	}
}
