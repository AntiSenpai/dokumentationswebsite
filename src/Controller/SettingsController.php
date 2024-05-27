<?php

namespace App\Controller;

use App\Form\EmailUpdateType;
use App\Form\PasswordChangeType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class SettingsController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/einstellungen', name: 'app_settings')]
    #[IsGranted('ROLE_PRAKTIKANT')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $users = $entityManager->getRepository(User::class)->findAll();
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
            'users' => $users,
            'currentPage' => 'settings',
            'emailForm' => $emailForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'isEmailVerificationEnabled' => $user->isEmailVerificationEnabled(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profile/upload', name: 'profile_upload', methods: ['POST'])]
    #[IsGranted('ROLE_PRAKTIKANT')]
    public function uploadProfilePicture(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
    $user = $this->getUser();
    if (!$user) {
        return $this->json(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
    }

    $imageFile = $request->files->get('imageFile'); 
    if ($imageFile) {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

        try {
            $imageFile->move(
                $this->getParameter('profile_image_directory'),
                $newFilename
            );
            $user->setProfilePicture($newFilename);
            $entityManager->persist($user);
            $entityManager->flush();
        
            return $this->json(['message' => 'File uploaded successfully', 'filename' => $newFilename]);
        } catch (FileException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    return $this->json(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/send-feedback', name: 'send_feedback', methods: ['POST'])]
    public function sendFeedback(Request $request, MailerInterface $mailer): Response {
    $data = json_decode($request->getContent(), true);
    $feedback = $data['feedback'];
    $url = $data['url'];

    $email = (new Email())
        ->from('bot@nnc-it.com')
        ->to('kh@nnc-it.com')
        ->subject('Feedback von: ' . $this->getUser()->getUsername())
        ->text($feedback . "\n\nSeite: " . $url);

    try {
        $mailer->send($email);
        return new JsonResponse(['message' => 'Feedback gesendet'], Response::HTTP_OK);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Mail send error'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }



}
