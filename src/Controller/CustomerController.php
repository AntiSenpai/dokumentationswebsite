<?php

namespace App\Controller;


use App\Repository\LocationRepository;
use App\Repository\CustomerDocumentationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Customer;
use App\Entity\GeneralInfo;
use App\Form\CustomerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\CustomerDocumentation;
use App\Entity\Location; // Add this line to import the Location entity
use Doctrine\Persistence\ManagerRegistry;

class CustomerController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/kundendoku', name: 'customer_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {

        $customer = new Customer();

        $createForm = $this->createForm(CustomerType::class, $customer);

        $searchTerm = $request->query->get('search', '');

        $customers = $entityManager->getRepository(Customer::class)->findBySearchTerm($searchTerm);

        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
            'searchTerm' => $searchTerm,
            'createForm' => $createForm->createView(),
        ]);
    }

    #[Route('/create', name: 'customer_create')]
public function create(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response {
    $data = json_decode($request->getContent(), true);

    if (!isset($data['name'], $data['adresse'], $data['technischerAnsprechpartner'], $data['vorOrtAnsprechpartner'], $data['email'], $data['stundensatz'])) {
        return $this->json(['success' => false, 'message' => 'Fehlende Daten.'], Response::HTTP_BAD_REQUEST);
    }

    $customer = new Customer();
    $customer->setName($data['name']);
    $customer->setEmail($data['email']);
    $customer->setStundensatz($data['stundensatz']);
    $customer->setCreatedAt(new \DateTime());
    $customer->setUpdatedAt(new \DateTime());
    $customer->setUpdatedBy($this->getUser());

    // Generierung der Suchnummer
    $customer->setSuchnummer('K' . rand(10000000, 99999999));

    // Zuweisung des technischen Ansprechpartners
    $techAnsprechpartner = $userRepository->find($data['technischerAnsprechpartner']);
    if (!$techAnsprechpartner) {
        return $this->json(['success' => false, 'message' => 'Technischer Ansprechpartner nicht gefunden.'], Response::HTTP_BAD_REQUEST);
    }
    $customer->setTechnischerAnsprechpartner($techAnsprechpartner);

    // Zuweisung des vor Ort Ansprechpartners
    $customer->setVorOrtAnsprechpartner($data['vorOrtAnsprechpartner']);

    $entityManager->persist($customer);

    // Hauptstandort
    $hauptstandort = new Location();
    $hauptstandort->setName($data['name']);
    $hauptstandort->setAdresse($data['adresse']);
    $hauptstandort->setIstHauptstandort(true);
    $hauptstandort->setCustomer($customer);
    $entityManager->persist($hauptstandort);

    // Unterstandorte
    foreach ($data['unterstandorte'] as $unterstandortData) {
        $unterstandort = new Location();
        $unterstandort->setName($data['name']);
        $unterstandort->setAdresse($unterstandortData['adresse']);
        $unterstandort->setIstHauptstandort(false);
        $unterstandort->setCustomer($customer);
        $entityManager->persist($unterstandort);
    }

    try {
        $entityManager->flush();
        return $this->json(['success' => true, 'message' => 'Kunde mit Standorten erfolgreich erstellt.']);
    } catch (\Exception $e) {
        return $this->json(['success' => false, 'message' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    

    #[Route('/customer/{id}', name: 'customer_detail')]
    public function detail(int $id, EntityManagerInterface $entityManager, LocationRepository $locationRepo, CustomerDocumentationRepository $docRepo): Response {
    $customer = $entityManager->getRepository(Customer::class)->find($id);
    if (!$customer) {
        throw $this->createNotFoundException('Der Kunde wurde nicht gefunden.');
    }

    // Hier nutzen wir die Beziehung, um alle Standorte zu diesem Kunden zu finden
    // Zugriff auf die Standorte eines Kunden
    $locations = $customer->getLocations();


    // Bereiten Sie eine Struktur vor, um die Dokumentationen organisiert nach Location-ID zu speichern
    $documentationByLocation = [];
    foreach ($locations as $location) {
        $documentation = $docRepo->findBy(['location' => $location]);
        $documentationByLocation[$location->getId()] = $documentation;
    }

    return $this->render('customer/detail.html.twig', [
        'customer' => $customer,
        'locations' => $locations,
        'documentationByLocation' => $documentationByLocation,
        'currentPage' => 'customerDetail'
    ]);
}  

#[Route('/list', name: 'customer_list')]
public function list(Request $request, ManagerRegistry $doctrine): Response
{
    try {
        $searchTerm = $request->query->get('search', '');
        $entityManager = $doctrine->getManager();
        $customerRepository = $entityManager->getRepository(Customer::class);

        if (!empty($searchTerm)) {
            $customers = $customerRepository->findBySearchTerm($searchTerm);
        } else {
            $customers = $customerRepository->findAll();
        }

        $data = [];
        foreach ($customers as $customer) {
            $data[] = [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'suchnummer' => $customer->getSuchnummer(),
            ];
        }

        return $this->json($data);
    } catch (\Exception $e) {
        // Log the exception message or handle it as needed
        return $this->json(['error' => 'Ein interner Serverfehler ist aufgetreten.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

#[Route('/customer/{locationId}/add-documentation', name: 'add_customer_documentation', methods: ['POST'])]
public function addDocumentation(Request $request, EntityManagerInterface $entityManager, $locationId): JsonResponse
{
    $content = $request->request->get('content');
    $sectionType = $request->request->get('sectionType');
    $location = $entityManager->getRepository(Location::class)->find($locationId);

    if (!$location) {
        return $this->json(['message' => 'Standort nicht gefunden!'], Response::HTTP_NOT_FOUND);
    }

    $documentation = new CustomerDocumentation();
    $documentation->setContent($content);
    $documentation->setSectionType($sectionType);
    $documentation->setLocation($location);
    $documentation->setCreatedAt(new \DateTime());
    $documentation->setUpdatedBy($this->getUser()); 

    $entityManager->persist($documentation);
    $entityManager->flush();

    return $this->json(['message' => 'Dokumentation erfolgreich hinzugefügt', 'id' => $documentation->getId()]);
}

#[Route('/save-documentation', name: 'save_customer_documentation', methods: ['POST'])]
public function saveDocumentation(Request $request, EntityManagerInterface $entityManager, CustomerDocumentationRepository $docRepo): JsonResponse
{
    // Daten aus der Anfrage extrahieren
    $data = json_decode($request->getContent(), true);
    
    // Sicherstellen, dass die erforderlichen Daten vorhanden sind
    if (!isset($data['documentId'], $data['content'])) {
        return $this->json(['message' => 'Fehlende Daten'], Response::HTTP_BAD_REQUEST);
    }

    $documentId = $data['documentId'];
    $content = $data['content'];

    // Finden oder erstellen Sie ein Dokumentationsobjekt basierend auf der documentId
    $documentation = $docRepo->find($documentId);
    if (!$documentation) {
        $documentation = new CustomerDocumentation();
        // Weitere erforderliche Eigenschaften für die neue Dokumentation setzen
    }

    // Inhalt und andere Daten aktualisieren
    $documentation->setContent(json_encode($content)); // Inhalt als JSON speichern
    $documentation->setUpdatedAt(new \DateTime());
    // Weitere Eigenschaften aktualisieren, falls erforderlich

    $entityManager->persist($documentation);
    $entityManager->flush();

    return $this->json(['message' => 'Dokumentation erfolgreich gespeichert', 'id' => $documentation->getId()]);
}

    #[Route('/api/customers', name: 'api_customers_list')]
    public function apiCustomersList(EntityManagerInterface $entityManager): Response
    {
        $customers = $entityManager->getRepository(Customer::class)->findAll();

        $data = array_map(function ($customer) {
            return [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'suchnummer' => $customer->getSuchnummer(),
                'createdAt' => $customer->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedBy' => $customer->getUpdatedBy(),
            ];
        }, $customers);
        return $this->json(['customers' => $data]);
    }

}