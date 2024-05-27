<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OTPHP\TOTP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/benutzer', name: 'app_user')]
    public function index(EntityManagerInterface $entityManager): JsonResponse
{
    $userRepository = $entityManager->getRepository(User::class);
    try {
        $data = [];
        foreach ($userRepository->findAll() as $user) {
            $data[] = [
                'username' => $user->getUsername(),
                'id' => $user->getId(),
            ];
        }

        return $this->json($data);
    } catch (\Exception $e) {
        // Log the exception message or handle it as needed
        return $this->json(['error' => 'Ein interner Serverfehler ist aufgetreten.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
#[Route('/benutzer/erstellen', name: 'create_user', methods: ['POST'])]
#[IsGranted('ROLE_ADMIN')]
public function createUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager) {
    $data = json_decode($request->getContent(), true);

    $user = new User();
    $user->setUsername($data['username']);
    $user->setEmail($data['email']);
    $user->setRoles($data['roles'] ?? ['ROLE_USER']); 
    $user->setIsActive(true);
    $user->setIsVerified(false);

    $now = new \DateTime();
    $user->setTimer($now->format('Y-m-d'));

    $confirmationToken = bin2hex(random_bytes(32));
    $user->setConfirmationToken($confirmationToken);

   
    $encodedPassword = $passwordHasher->hashPassword($user, $data['password']);
    $user->setPassword($encodedPassword);

    $entityManager->persist($user);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Benutzer wurde erfolgreich erstellt.']);
}

    #[Route('/benutzer/totp-generator', name: 'generate_totp')]
    public function generateTotp(Security $security, EntityManagerInterface $entityManager): JsonResponse {
    $user = $security->getUser();

    if (!$user instanceof User) {
        throw $this->createAccessDeniedException("Nicht eingeloggt.");
    }

    if (!$user->getTotpSecret()) {
        // Kein Secret vorhanden, erstelle ein neues
        $totp = TOTP::create();
        $totp->setLabel('Kundendoku'); 
        $user->setTotpSecret($totp->getSecret());
        $entityManager->persist($user);
        $entityManager->flush();

        $qrCodeUri = $totp->getProvisioningUri();

        return new JsonResponse(['qrCodeUri' => $qrCodeUri, 'isNewSecret' => true]);
    } else {
        // Wenn ein Secret existiert, setze das existierende Secret und generiere die URI
        $totp = TOTP::create($user->getTotpSecret());
        $totp->setLabel('Kundendoku');
        $qrCodeUri = $totp->getProvisioningUri();
        $user->setIsTotpEnabled(true);
        $entityManager->persist($user);
        $entityManager->flush();
        return new JsonResponse(['qrCodeUri' => $qrCodeUri, 'isNewSecret' => false]);
    }
    }

    #[Route('/benutzer/qr-code', name: 'retrieve_qr_code')]
    public function retrieveQrCode(EntityManagerInterface $entityManager): JsonResponse
    {
    $user = $this->getUser();
    if (!$user) {
        return $this->json(['error' => 'Nicht authentifiziert'], Response::HTTP_UNAUTHORIZED);
    }

    $secret = $user->getTotpSecret();
    if (!$secret) {
        return $this->json(['error' => 'Kein TOTP-Secret vorhanden'], Response::HTTP_BAD_REQUEST);
    }

    $totp = TOTP::create($secret);
    $totp->setLabel('NNC-IT ' . $user->getUsername());
    $qrCodeUri = $totp->getProvisioningUri();

    return $this->json(['qrCodeUri' => $qrCodeUri]);
    }

    #[Route('/benutzer/totp-setup', name: 'user_totp_setup')]
    public function totpSetup(Security $security): Response {
        $user = $security->getUser();

        if(!$user instanceof User) {
            throw $this->createAccessDeniedException("Nicht eingeloggt.");
        }

        $totp = TOTP::create($user->getTotpSecret());
        $totp->setLabel('NNC-IT'. $user->getUsername());

        $qrCodeUri = $totp->getQrCodeUri('google', 'NNC-IT'. $user->getUsername());

        return $this->render('user/totp_setup.html.twig', [
            'qrCodeUri' => $qrCodeUri,
        ]);
    }

    #[Route('/benutzer/totp-verify', name: 'user_totp_verify', methods: ['POST'])]
    public function verifyTotpCode(Request $request, Security $security, EntityManagerInterface $entityManager): JsonResponse {
    $user = $security->getUser();
    $data = json_decode($request->getContent(), true);
    $code = $data['totpCode'];

    $totp = TOTP::create($user->getTotpSecret());

    if ($totp->verify($code)) {
        $user->setIsTotpEnabled(true);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'TOTP wurde erfolgreich aktiviert.']);
    } else {
        return new JsonResponse(['success' => false, 'message' => 'Der eingegebene Code ist ungültig.'], 422); // HTTP 422 Unprocessable Entity
    }
    }

    #[Route('/benutzer/deaktiviere-totp', name: 'disable_totp')]
    public function disableTotp(Security $security, EntityManagerInterface $entityManager): JsonResponse
    {
    $user = $security->getUser();
    if (!$user instanceof User) {
        return new JsonResponse(['error' => 'Nicht eingeloggt'], Response::HTTP_FORBIDDEN);
    }

    $user->setIsTotpEnabled(false);
    $entityManager->persist($user);
    $entityManager->flush();

    return new JsonResponse(['message' => 'TOTP wurde erfolgreich deaktiviert.', 'type' => 'success']);
    }

    #[Route('/totp-verify', name: 'totp_verify')]
    public function totpVerify(Security $security): Response {
    $user = $security->getUser();

    if($user && $user->isTotpEnabled()) {
        return $this->render('security/totp_verify.html.twig', [
            'user' => $user,
        ]);
    }

    return $this->redirectToRoute('dokumentation');
    }

    #[Route('/verify-totp-code', name: 'verify_totp_code', methods: ['POST'])]
    public function verifyTotpOnLogin(Request $request, Security $security, EntityManagerInterface $entityManager): Response {
    $user = $security->getUser();
    $totpCode = $request->request->get('totp_code');

    if ($user && $user->isTotpEnabled()) {
        $totp = TOTP::create($user->getTotpSecret());
        if ($totp->verify($totpCode)) {
            // Code ist korrekt
            $user->setIsVerified(true);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('dokumentation');
        } else {
            // Code ist falsch
            $this->addFlash('error', 'Falscher TOTP-Code.');
            return $this->redirectToRoute('totp_verify');
        }
    }

    return $this->redirectToRoute('login');
    }

    #[Route('/benutzer/toggle-totp-status', name: 'toggle_totp_status', methods: ['POST'])]
public function toggleTotpStatus(Request $request, Security $security, EntityManagerInterface $entityManager): JsonResponse {
    $user = $security->getUser();
    $data = json_decode($request->getContent(), true);
    $enable = $data['enable'];

    if (!$user instanceof User) {
        return new JsonResponse(['error' => 'Nicht eingeloggt'], Response::HTTP_FORBIDDEN);
    }

    $user->setIsTotpEnabled($enable);
    if ($enable) {
        $user->setIsEmailVerificationEnabled(false); // Deaktiviere E-Mail, wenn TOTP aktiviert wird
    }
    $entityManager->persist($user);
    $entityManager->flush();

    if ($enable && !$user->getTotpSecret()) {
        // Kein Secret vorhanden, erstelle ein neues
        $totp = TOTP::create();
        $totp->setLabel('Kundendoku'); 
        $user->setTotpSecret($totp->getSecret());
        $entityManager->persist($user);
        $entityManager->flush();

        $qrCodeUri = $totp->getProvisioningUri();

        return new JsonResponse(['qrCodeUri' => $qrCodeUri, 'isNewSecret' => true]);
    } else {
        return new JsonResponse(['isNewSecret' => false]);
    }
}



    #[Route('/benutzer/toggle-email-verification', name: 'toggle_email_verification', methods: ['POST'])]
    public function toggleEmailVerification(Request $request, Security $security, EntityManagerInterface $entityManager): JsonResponse {
    $user = $security->getUser();
    $data = json_decode($request->getContent(), true);
    $enable = $data['enable'];

    if (!$user instanceof User) {
        return new JsonResponse(['error' => 'Nicht eingeloggt'], JsonResponse::HTTP_FORBIDDEN);
    }

    $user->setIsEmailVerificationEnabled($enable);
    if ($enable) {
        $user->setIsTotpEnabled(false); // Deaktiviere TOTP, wenn E-Mail aktiviert wird
    }
    $entityManager->persist($user);
    $entityManager->flush();

    return new JsonResponse(['message' => 'E-Mail-Verifizierung wurde ' . ($enable ? 'aktiviert' : 'deaktiviert')]);
    }
}
