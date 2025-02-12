<?php

namespace App\Controller;

use App\Entity\Month;
use App\Entity\Advice;
use App\Repository\MonthRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;

use function Symfony\Component\Clock\now;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AdviceController extends AbstractController
{
    public function __construct(
        private MonthRepository $monthRepository,
        private SerializerInterface $serializer,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator
    ) {}

    #[Route('/api/conseil/{id}', name: 'api_advice', methods: ['GET'], defaults: ['id' => null])]
    public function getAdviceCurrentMonth(
        Month $month = null
    ): JsonResponse {

        if ($month) {
            $jsonMonth = $this->serializer->serialize($month, 'json', ['groups' => 'getAdvicesOfTheMonth']);
            return new JsonResponse($jsonMonth, Response::HTTP_OK, [], true);
        }

        $currentMonth = intval(now()->format('n'));

        $month = $this->monthRepository->find($currentMonth);

        $jsonMonth = $this->serializer->serialize($month, 'json');
        return new JsonResponse($jsonMonth, Response::HTTP_OK, [], true);
    }

    #[Route('/api/conseil', name: 'api_advice_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les autorisations nécessaires.", statusCode: 403)]
    public function create(
        Request $request
    ): JsonResponse {
        $advice = $this->serializer->deserialize($request->getContent(), Advice::class, 'json', [
            'ignored_attributes' => ['month']
        ]);

        $errors = $this->validator->validate($advice);
        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();

        $idMonth = $content['month'] ?? -1;

        foreach ($idMonth as $value) {
            $advice->addMonth($this->monthRepository->find($value));
        }

        $this->em->persist($advice);
        $this->em->flush();

        $jsonAdvice = $this->serializer->serialize($advice, 'json', ['groups' => 'getMonthsOfAdvice']);

        return new JsonResponse($jsonAdvice, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/conseil/{id}', name: 'api_advice_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les autorisations nécessaires.", statusCode: 403)]
    public function update(
        Request $request,
        Advice $currentAdvice
    ): JsonResponse {
        $updatedAdvice = $this->serializer->deserialize(
            $request->getContent(),
            Advice::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $currentAdvice,
                'ignored_attributes' => ['month']
            ]
        );

        $content = $request->toArray();

        $idMonth = $content['month'] ?? null;

        if (is_array($idMonth)) {
            // Récupérer les mois actuels.
            $currentMonths = $updatedAdvice->getMonth()->toArray();

            // Transformer en tableau d'id.
            $currentMonthIds = array_map(fn($month) => $month->getId(), $currentMonths);

            // Supprimer les mois qui ne sont plus dans la nouvelle liste.
            foreach ($currentMonths as $month) {
                if (!in_array($month->getId(), $idMonth)) {
                    $updatedAdvice->removeMonth($month);
                }
            }

            // Ajouter les nouveaux mois qui ne sont pas déjà présents.
            foreach ($idMonth as $value) {
                if (!in_array($value, $currentMonthIds)) {
                    $updatedAdvice->addMonth($this->monthRepository->find($value));
                }
            }
        }

        $this->em->persist($updatedAdvice);
        $this->em->flush();

        return new JsonResponse([
            'message' => "Le conseil avec l'id {$updatedAdvice->getId()} a bien été modifier."
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/api/conseil/{id}', name: 'api_advice_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les autorisations nécessaires.", statusCode: 403)]
    public function delete(
        Advice $advice
    ): JsonResponse {
        $this->em->remove($advice);
        $this->em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_OK);
    }
}
