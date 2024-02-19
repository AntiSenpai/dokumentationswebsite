<?php
// src/Controller/TimeTrackingController.php
namespace App\Controller;

use App\Repository\ZeiterfassungRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Zeiterfassung;
use App\Entity\User;
use App\Entity\Customer;

class TimeTrackingController extends AbstractController
{
    /**
    * @Route("/zeiterfassung", name="zeiterfassung")
    */
public function index(ZeiterfassungRepository $zeiterfassungRepository, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();

    if (!$user) {
        throw $this->createNotFoundException('Benutzer nicht angemeldet');
    }

    $currentEntry = $zeiterfassungRepository->getCurrentEntryByUser($user);

    // Angenommen, die Zeiterfassung ist direkt mit User verknüpft und nicht mit Mitarbeiter
    $zeiterfassungseintraege = $entityManager->getRepository(Zeiterfassung::class)->findBy([
        'user' => $user
    ]);

    // Fetch customers from database
    $customerRepository = $entityManager->getRepository(Customer::class);
    $customers = $customerRepository->findAll();

    return $this->render('time_tracking/index.html.twig', [
        'zeiterfassungseintraege' => $zeiterfassungseintraege,
        'currentEntry' => $currentEntry,
        'customers' => $customers
    ]);
}

    public function einsatzAuswaehlen(Request $request, EntityManagerInterface $entityManager, ZeiterfassungRepository $zeiterfassungRepository) {
        $user = $this->getUser();
        $currentEntry = $zeiterfassungRepository->getCurrentEntryByUser($user, 'Einsatz');

        if(!$currentEntry) {
            $zeiterfassung = new Zeiterfassung();
            $zeiterfassung->setStartzeitpunkt(new \DateTime());
            $zeiterfassung->setTyp('Einsatz');
            $zeiterfassung->setUser($user);

            $entityManager->persist($zeiterfassung);
            $entityManager->flush();

            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false, 'message' => 'Es läuft bereits ein Einsatz für diesen User']);
    }

    public function getCustomers(Request $request) {
        $entityManager = $this->getDoctrine()->getManager();
        $customerRepository = $entityManager->getRepository(Customer::class);
        $customers = $customerRepository->findAll();

        $response = [];
        foreach($customers as $customer) {
            $response[] = [
                'id' => $customer->getId(),
                'name' => $customer->getName()
            ];
        }

        return $this->json($response);
    }

    public function startArbeitszeit(Request $request, EntityManagerInterface $entityManager, ZeiterfassungRepository $zeiterfassungRepository): Response {
        $user = $this->getUser();

        // Stoppt den Einsatztimer, falls aktiv
        $einsatzEntry = $zeiterfassungRepository->getCurrentEntryByUser($user, 'Einsatz');
        if($einsatzEntry) {
            $einsatzEntry->setEndzeitpunkt(new \DateTime());
            $entityManager->flush();
        }

        // startet die Arbeitszeit
        $currentEntry = $zeiterfassungRepository->getCurrentEntryByUser($user, 'Arbeit');
        if($currentEntry) {
            // wenn es bereits einen Eintrag gibt, wird die Startzeit angepasst:
            $currentEntry->setStartzeitpunkt(new \DateTime());
            $entityManager->flush();
            return new Response('Arbeitszeit gestartet');
        }

        $zeiterfassung = new Zeiterfassung();
        $zeiterfassung->setStartzeitpunkt(new \DateTime());
        $zeiterfassung->setTyp('Arbeit');
        $zeiterfassung->setUser($user);

        $entityManager->persist($zeiterfassung);
        $entityManager->flush();

        return new Response('Arbeitszeit gestartet');
    }

    public function pauseArbeitszeit(Request $request, EntityManagerInterface $entityManager, ZeiterfassungRepository $zeiterfassungRepository)
{
    $user = $this->getUser();

    $currentEntry = $zeiterfassungRepository->getCurrentEntryByUser($user, 'Arbeit');
    if (!$currentEntry) {
        return new Response('Keine aktive Arbeitszeit gefunden.');
    }

    // Stoppe die Arbeitszeit
    $currentEntry->setEndzeitpunkt(new \DateTime());
    $entityManager->flush();

    // Starte die Pause
    $pauseEntry = new Zeiterfassung();
    $pauseEntry->setStartzeitpunkt(new \DateTime());
    $pauseEntry->setTyp('Pause');
    $pauseEntry->setUser($user);

    $entityManager->persist($pauseEntry);
    $entityManager->flush();

    // Fortsetze den Einsatz-Timer
    $einsatzEntry = $zeiterfassungRepository->getCurrentEntryByUser($user, 'Einsatz');
    if ($einsatzEntry) {
        $einsatzEntry->setStartzeitpunkt(new \DateTime());
        $entityManager->flush();
    }

    return new Response('Pause gestartet');
}


public function stopArbeitszeit(Request $request, EntityManagerInterface $entityManager, ZeiterfassungRepository $zeiterfassungRepository)
{
    $user = $this->getUser();

    // Beende die Pause, falls aktiv
    $pauseEntry = $zeiterfassungRepository->getCurrentEntryByUser($user, 'Pause');
    if ($pauseEntry) {
        $pauseEntry->setEndzeitpunkt(new \DateTime());
        $entityManager->flush();
    }

    // Beende die Arbeitszeit
    $currentEntry = $zeiterfassungRepository->getCurrentEntryByUser($user, 'Arbeit');
    if (!$currentEntry) {
        return new Response('Keine aktive Arbeitszeit gefunden.');
    }

    $currentEntry->setEndzeitpunkt(new \DateTime());
    $entityManager->flush();

    // Berechne die Arbeitszeit
    $start = $currentEntry->getStartzeitpunkt();
    $end = $currentEntry->getEndzeitpunkt();
    $duration = $end->diff($start);

    // Speichere die Arbeitszeit in der Datenbank
    $arbeitszeit = new Zeiterfassung();
    $arbeitszeit->setStartzeitpunkt($start);
    $arbeitszeit->setEndzeitpunkt($end);
    $arbeitszeit->setArbeitszeit($duration->format('%H:%I:%S'));
    $arbeitszeit->setUser($user);

    $entityManager->persist($arbeitszeit);
    $entityManager->flush();

    return new Response('Arbeitszeit gestoppt');
}

}