<?php

namespace App\Service\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiOpenWeatherService
{
    private $APIkey = 'e2b2904deb1eff42eb3971f85d049123';

    public function __construct(
        private HttpClientInterface $client
    ) {}

    public function getOpenWeatherApi($cityName): ?array
    {
        $arrayData = $this->getGeocodingApi($cityName);

        if ($arrayData !== []) {
            $lat = $arrayData[0]['lat'];
            $lon = $arrayData[0]['lon'];

            $response = $this->client->request(
                'GET',
                'https://api.openweathermap.org/data/3.0/onecall?lat=' . $lat . '&lon=' . $lon . '&appid=' . $this->APIkey
            );

            return $response->toArray();
        }

        return null;
    }

    private function getGeocodingApi(string $cityName): array
    {
        $response = $this->client->request(
            'GET',
            'http://api.openweathermap.org/geo/1.0/direct?q=' . $cityName . '&appid=' . $this->APIkey
        );

        return $response->toArray();
    }
}
