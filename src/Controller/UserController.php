<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Api\CallApiCartoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private CallApiCartoService $callApiCartoService,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}

    #[Route('/api/user', name: 'api_user', methods: ['POST'])]
    public function user(
        Request $request
    ): JsonResponse {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $password = $user->getPassword();
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password));

        $user->setCity($this->callApiCartoService->getCity($user->getPostalCode()));

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse([
            'message' => "L'utilisateur {$user->getEmail()} a été créé avec succès."
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/user/{id}', name: 'api_user_update', methods: ['PUT'])]
    public function update(
        Request $request,
        User $currentUser
    ): JsonResponse {
        $updatedUser = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );

        $password = $updatedUser->getPassword();
        $updatedUser->setPassword($this->userPasswordHasher->hashPassword($updatedUser, $password));

        $this->em->persist($updatedUser);
        $this->em->flush();

        return new JsonResponse([
            'message' => "Le compte {$updatedUser->getEmail()} a bien été modifier."
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/api/user/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    public function delete(
        User $user
    ): JsonResponse {
        $this->em->remove($user);
        $this->em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_OK);
    }
}
