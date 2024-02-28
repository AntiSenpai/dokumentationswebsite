<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TotpAuthenticatorInterface $totpAuthenticator;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, TotpAuthenticatorInterface $totpAuthenticator, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->totpAuthenticator = $totpAuthenticator;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate a new TOTP secret
            $totpSecret = $this->totpAuthenticator->generateSecret();

            // Set the TOTP secret using the setter in the User entity
            $user->setTotpAuthenticationSecret($totpSecret);

            // Set the isActive property
            $user->setIsActive(true); // or set it to false if you prefer

            $timer = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            // Set the timer property (if needed)
            $user->setTimer($timer); // or set it to another value if you prefer

            // encode the plain password
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles(['ROLE_USER']);
            $user->setUsername($form->get('username')->getData());
            $user->setEmail($form->get('email')->getData());

            // Add any other relevant field mappings here

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}