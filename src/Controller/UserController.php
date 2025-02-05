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
}
