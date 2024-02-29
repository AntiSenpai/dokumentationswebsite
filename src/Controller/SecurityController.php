<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\TwoFactorInterface;

class SecurityController extends AbstractController
{

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error
    ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout(EntityManagerInterface $entityManager): void {
    // Hier Logik, um den is_verified Status des aktuellen Benutzers auf false zu setzen
    $user = $this->getUser();
    if ($user && $user->isVerified() === true) {
        $user->setIsVerified(false);
        $entityManager->flush();
    }

    throw new \LogicException('Logout route should not be reachable.');
}

    #[Route('/trigger_2fa', name: 'trigger_2fa')]
    public function trigger2fa(): Response {
        // Annahme, dass $this->getUser() den angemeldeten Benutzer zurückgibt
        $userEmail = $this->getUser() ? $this->getUser()->getEmail() : null;
    
        return $this->render('security/trigger2fa.html.twig', [
            'userEmail' => $userEmail,
        ]);
    }

    #[Route(path: '/confirm_2fa/{token}', name: 'app_confirm_2fa')]
    public function confirm(string $token, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(['confirmationToken' => $token]);
        if($user) {
            $user->setConfirmationToken(null);
            $user->setIsVerified(true);
            $entityManager->flush();
            return $this->render('security/confirmed.html.twig');
        } else {
            return $this->render('security/login.html.twig', ['error' => 'Invalid or expired token']);
        }
    }

    #[Route(path: '/is_confirmed/{email}', name: 'app_is_confirmed')]
    public function isConfirmed(string $email, UserRepository $userRepository): JsonResponse
    {
    $user = $userRepository->findOneByEmail($email);
    return new JsonResponse(['confirmed' => $user ? $user->isVerified() : false]);
    }

}
