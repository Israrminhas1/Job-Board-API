<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

final class RegistrationController extends AbstractController
{
    #[Route(path: "/api/v1/register", methods: ["POST"])]
    #[OA\Tag(name: 'auth')]
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $params = json_decode($request->getContent());

        $user = new User();

        $hashedPassword = $hasher->hashPassword($user, $params->password);
        $user->setPassword($hashedPassword);

        $user->setEmail($params->email);
        $user->setUsername($params->email);

        $userRepository->save($user, true);

        return $this->json(
            (array)new ResponseDto('User created', ['email' => $user->getEmail()], 201)
        );
    }
}
