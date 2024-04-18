<?php

namespace App\Controller;

use App\Form\EmailUpdateType;
use App\Form\PasswordChangeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/einstellungen', name: 'app_settings')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $emailForm = $this->createForm(EmailUpdateType::class, $user);
        $passwordForm = $this->createForm(PasswordChangeType::class, $user);

        $emailForm->handleRequest($request);
        $passwordForm->handleRequest($request);

        if($emailForm->isSubmitted() && $emailForm->isValid()) {
            $email = $emailForm->get('email')->getData();
            if ($email !== $user->getEmail()) {
                $user->setEmail($email);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Email geändert!');
            } else {
                $this->addFlash('info', 'Das ist bereits Ihre aktuelle E-Mail.');
            }
        }
        
        if($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $currentPassword = $passwordForm->get('current_password')->getData();
            $newPassword = $passwordForm->get('new_password')->getData();
            
            if(!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Das aktuelle Passwort ist nicht korrekt!');
            }
        
            if($currentPassword === $newPassword) {
                $this->addFlash('info', 'Das neue Passwort darf nicht das gleiche wie das aktuelle sein.');
            }
        
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Passwort geändert!');
        }

        return $this->render('settings/index.html.twig', [
            'controller_name' => 'SettingsController',
            'currentPage' => 'settings',
            'emailForm' => $emailForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'isEmailVerificationEnabled' => $user->isEmailVerificationEnabled(),
            'user' => $this->getUser(),
        ]);
    }
}
