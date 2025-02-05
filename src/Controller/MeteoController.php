<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Api\CallApiOpenWeatherService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class MeteoController extends AbstractController
{
    public function __construct(
        private CallApiOpenWeatherService $callApiOpenWeatherService
    ) {}

    #[Route('/api/meteo', name: 'api_meteo', methods: ['GET'])]
    public function getUserWeather(): JsonResponse
    {
        /** @var ?User $test */
        $user = $this->getUser();

        if ($user instanceof User) {
            $city = $user->getCity();
        }

        $weatherData = $this->callApiOpenWeatherService->getOpenWeatherApi($city);

        if (is_array($weatherData)) {
            return new JsonResponse($weatherData, JsonResponse::HTTP_OK);
        }

        return new JsonResponse([
            'message' => "Information sur la ville introuvable."
        ], Response::HTTP_BAD_REQUEST);
    }
}
