<?php

namespace App\Controller;

use App\Entity\Month;
use App\Repository\MonthRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

final class AdviceController extends AbstractController
{
    public function __construct(
        private MonthRepository $monthRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('/api/conseil/{id}', name: 'api_advice_month', methods: ['GET'])]
    public function getPerMonth(Month $month): JsonResponse
    {
        $jsonMonth = $this->serializer->serialize($month, 'json', ['groups' => 'getAdvice']);
        return new JsonResponse($jsonMonth, Response::HTTP_OK, [], true);
    }
}
