<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
    public function login(AuthenticationUtils $authenticationUtils, Security $security, EntityManagerInterface $entityManager): Response {
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();
    $user = $security->getUser();

    if($user) {
        if($user->isTotpEnabled() && !$user->isVerified()) {
            return $this->redirectToRoute('totp_verify');
        }

        if($user->isEmailVerificationEnabled() && !$user->isVerified()) {
            return $this->redirectToRoute('trigger_2fa');
        }

        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->redirectToRoute('dokumentation');
    }

    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error
    ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void {
    $user = $this->getUser();
    if($user->isVerified()) {
        $user->setIsVerified(false);
    }
    throw new \LogicException('Logout route should not be reachable.');
    }


    #[Route('/trigger_2fa', name: 'trigger_2fa')]
public function trigger2fa(Security $security): Response {
    $user = $security->getUser();

    if (!$user || $user->isVerified()) {
        // Wenn der Benutzer nicht vorhanden ist oder bereits verifiziert wurde, leiten Sie zur Hauptseite um
        return $this->redirectToRoute('app_main');
    }

    $userEmail = $user->getEmail();

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
    public function isConfirmed(string $email, UserRepository $userRepository): JsonResponse {
    $user = $userRepository->findOneByEmail($email);
    if (!$user) {
        return new JsonResponse(['confirmed' => false, 'error' => 'User not found']);
    }
    return new JsonResponse(['confirmed' => $user->isVerified()]);
}


}
