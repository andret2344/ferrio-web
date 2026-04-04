<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\HolidayConnector;
use App\Service\TimezoneResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UpcomingController extends AbstractController
{
	public function __construct(
		private readonly HolidayConnector  $connector,
		private readonly TimezoneResolver  $timezoneResolver,
	) {}

	#[Route('/{_locale}/upcoming', name: 'upcoming_redirect', requirements: ['_locale' => 'en|pl'])]
	public function redirectToDefault(): Response
	{
		return $this->redirectToRoute('upcoming', ['week' => 0]);
	}

	#[Route('/{_locale}/upcoming/{week}', name: 'upcoming', requirements: ['_locale' => 'en|pl', 'week' => '-?\d+'])]
	public function show(int $week, Request $request): Response
	{
		$lang = $request->getLocale();
		$tz = $this->timezoneResolver->resolve($request);
		$days = $this->connector->getUpcomingDaysSafe($week, $lang, $tz);

		$daysList = $days ?? [];
		$firstDate = $daysList !== [] ? $daysList[0]->formattedDate : '';
		$lastDate = $daysList !== [] ? $daysList[count($daysList) - 1]->formattedDate : '';

		$response = $this->render('upcoming/show.html.twig', [
			'weekOffset' => $week,
			'days' => $daysList,
			'hasError' => $days === null,
			'lang' => $lang,
			'firstDate' => $firstDate,
			'lastDate' => $lastDate,
		]);

		$response->setSharedMaxAge(3600);
		$response->headers->set('Cache-Control', 'public, max-age=900, s-maxage=3600, stale-while-revalidate=86400');
		$response->setEtag(md5($response->getContent()));

		return $response;
	}
}
