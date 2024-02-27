<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/trigger_2fa', name: 'trigger_2fa')]
    public function trigger2fa(): Response
    {
        return $this->render('security/trigger2fa.html.twig');
    }

    #[Route(path: '/2fa/verify', name: 'verify_2fa', methods: ['POST'])]
    public function verify2fa(Request $request, TwoFactorInterface $twoFactorService): Response
    {
    $code = $request->request->get('code');
    $user = $this->getUser();
    if ($user && $twoFactorService->checkCode($user, $code)) {
        $request->getSession()->set('2fa_verified', true);
        return $this->redirectToRoute('home');
    } else {
        // Hier könnten Sie eine Fehlermeldung zurückgeben oder den Benutzer auf die 2FA-Seite zurückleiten
        return $this->redirectToRoute('trigger_2fa', ['error' => 'Ungültiger 2FA-Code']);
    }
}
}
