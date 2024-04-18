<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    private MailerInterface $mailer; // Hinzufügen des Mailer-Service

    public function __construct(
        UrlGeneratorInterface $urlGenerator, 
        UserRepository $userRepository, 
        EntityManagerInterface $entityManager,
        MailerInterface $mailer // Hinzufügen des Mailer-Service
    ) {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?RedirectResponse
    {
    $user = $token->getUser();
    if ($user instanceof User) {
      
        if ($user->isTotpEnabled() && !$user->isVerified()) {
            return new RedirectResponse($this->urlGenerator->generate('totp_verify'));
        }
       
        else if ($user->isEmailVerificationEnabled() && !$user->isVerified()) {
            $confirmationToken = bin2hex(random_bytes(32));
            $user->setConfirmationToken($confirmationToken);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $verificationUrl = $this->urlGenerator->generate('app_confirm_2fa', ['token' => $confirmationToken], UrlGeneratorInterface::ABSOLUTE_URL);
            $email = (new Email())
                ->from('bot@nnc-it.com')
                ->to($user->getEmail())
                ->subject('Anmeldebestätigung')
                ->html($this->generateEmailContent($verificationUrl));

            $this->mailer->send($email);
          
            return new RedirectResponse($this->urlGenerator->generate('trigger_2fa'));
        }
        
        else {
           
            if (!$user->isVerified()) {
                $user->setIsVerified(true);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

            return new RedirectResponse($this->urlGenerator->generate('home'));
        }
    }

   
    return new RedirectResponse($this->urlGenerator->generate('app_logout'));
}



private function generateEmailContent(string $verificationUrl): string
{
    return '
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Email Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f2f2f2; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 40px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center; }
                h1 { color: #333333; font-size: 28px; margin-top: 0; }
                p { color: #666666; font-size: 18px; margin-bottom: 20px; }
                .btn { display: inline-block; margin-top: 20px; padding: 12px 24px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; font-size: 18px; transition: background-color 0.3s ease; }
                .btn:hover { background-color: #0056b3; }
                .logo { margin-top: 40px; max-width: 200px; }
            </style>
        </head>
        <body>
            <div class="container">
                <img src="{{ asset("images/detaillogo.png") }}" alt="NNC-IT Logo" class="logo">
                <h1>Bestätige deine Anmeldung</h1>
                <br>
                <p><a href="' . $verificationUrl . '" class="btn">Jetzt bestätigen</a></p>
                <p>Nach erfolgreicher Bestätigung kannst du den Tab schließen.</p>
            </div>
        </body>
        </html>
    ';
}

    
    

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}