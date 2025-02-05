<?php

namespace App\Service\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiCartoService
{
    public function __construct(
        private HttpClientInterface $client
    ) {}

    public function getCity(string $postalCode): string
    {
        $response = $this->client->request(
            'GET',
            'https://apicarto.ign.fr/api/codes-postaux/communes/' . $postalCode
        );

        return $response->toArray()[0]['nomCommune'];
    }
}
