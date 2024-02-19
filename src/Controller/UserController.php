<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
{
    $userRepository = $entityManager->getRepository(User::class);
    try {
        $data = [];
        foreach ($userRepository->findAll() as $user) {
            $data[] = [
                'username' => $user->getUsername(),
            ];
        }

        return $this->json($data);
    } catch (\Exception $e) {
        // Log the exception message or handle it as needed
        return $this->json(['error' => 'Ein interner Serverfehler ist aufgetreten.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
}