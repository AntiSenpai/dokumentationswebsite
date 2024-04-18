<?php

namespace App\Controller;


use App\Repository\CustomerRepository;
use App\Repository\LocationRepository;
use App\Repository\CustomerDocumentationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Customer;
use App\Entity\GeneralInfo;
use App\Form\CustomerType;
use Ramsey\Uuid\Nonstandard\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\CustomerDocumentation;
use App\Entity\Location;
use Doctrine\Persistence\ManagerRegistry;

class CustomerController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/kundendoku', name: 'customer_index')]
    public function index(Request $request, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): Response
    {

        $customer = new Customer();

        $createForm = $this->createForm(CustomerType::class, $customer);

        // Parameter für Paginierung
        $currentPage = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $customersPaginator = $customerRepository->getPaginatedCustomers($limit, $currentPage);
        $totalCustomers = $customersPaginator->count();
        $totalPages = ceil($totalCustomers / $limit);

        return $this->render('customer/index.html.twig', [
            'customers' => $customersPaginator,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            //'searchTerm' => $searchTerm,
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
    $customer->setSuchnummer('K' . rand(10000, 999999));

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
        $unterstandort->setUnterstandort($hauptstandort);
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
    $sectionTypes = ['allgemein', 'netz', 'server', 'clients', 'userpwd', 'routerfirewall', 'provider', 'remotemaintenance', 'backup', 'ups', 'antivirus', 'applicationsoftware', 'otherinfo'];
    $documentationData = [];

    $unterstandorte = $locationRepo->findBy([
        'customer' => $customer,
        'istHauptstandort' => false
    ]);

    foreach ($sectionTypes as $sectionType) {
        foreach(['default', 'table'] as $cardType) {
            $doc = $docRepo->findOneBy([
                'customer' => $customer, 
                'sectionType' => $sectionType, 
                'cardType' => $cardType
        ]);
            
            if(!$doc) {
                // Wenn keine Dokumentation gefunden wurde, erstelle eine neue
                $doc = new CustomerDocumentation();
                $doc->setCustomer($customer);
                $doc->setSectionType($sectionType);
                $doc->setCardType($cardType); 
                $doc->setCardId(Uuid::uuid4()->toString());
                $doc->setContent('');
                $doc->setCreatedAt(new \DateTime());
                $doc->setUpdatedAt(new \DateTime());
                $doc->setUpdatedBy($this->getUser());
                $entityManager->persist($doc);
            }

            // Sammeln der Daten für die Ausgabe, strukturiert nach sectionType und cardType
            $documentationData[$sectionType][$cardType] = [
                'cardId' => $doc->getCardId(),
                'content' => $doc->getContent() ? json_decode($doc->getContent(), true) : null,
                'sectionType' => $doc->getSectionType(),
            ];
        }
    }

    $entityManager->flush();

    return $this->render('customer/detail.html.twig', [
        'customer' => $customer,
        'unterstandorte' => $unterstandorte,
        'documentationData' => $documentationData,
        'currentPage' => 'customerDetail'
    ]);
}

#[Route('/api/kunden/suche', name: 'customer_list', methods: ['GET'])]
public function list(Request $request, ManagerRegistry $doctrine, CustomerRepository $customerRepository): JsonResponse
{
    $searchTerm = $request->query->get('search', '');

    try {
        $customers = $customerRepository->findBySearchTerm($searchTerm);
        $data = array_map(function ($customer) {
            return [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'suchnummer' => $customer->getSuchnummer(),
                'createdAt' => $customer->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $customer->getUpdatedAt()->format('H:i'),
                'updatedBy' => $customer->getUpdatedBy() ? $customer->getUpdatedBy()->getUsername() : 'N/A',
            ];
        }, $customers);

        return $this->json($data);
    } catch (\Exception $e) {
        return $this->json(['error' => 'Bitte wende dich an den Ersteller.' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

##[Route('/customer/{locationId}/add-documentation', name: 'add_customer_documentation', methods: ['POST'])]
#public function addDocumentation(Request $request, EntityManagerInterface $entityManager, $locationId): JsonResponse
#{
#    $content = $request->request->get('content');
#    $sectionType = $request->request->get('sectionType');
#    $location = $entityManager->getRepository(Location::class)->find($locationId);
#
#    if (!$location) {
#        return $this->json(['message' => 'Standort nicht gefunden!'], Response::HTTP_NOT_FOUND);
#    }
#
#    $documentation = new CustomerDocumentation();
#    $documentation->setContent($content);
#    $documentation->setSectionType($sectionType);
#    $documentation->setLocation($location);
#    $documentation->setCreatedAt(new \DateTime());
#    $documentation->setUpdatedBy($this->getUser()); 
#
#    $entityManager->persist($documentation);
#    $entityManager->flush();
#
#    return $this->json(['message' => 'Dokumentation erfolgreich hinzugefügt', 'id' => $documentation->getId()]);
# }

#[Route('/save-card', name: 'save_customer_documentation', methods: ['POST'])]
public function saveDocumentation(Request $request, EntityManagerInterface $entityManager, CustomerDocumentationRepository $docRepo, CustomerRepository $customerRepo): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    
    // Prüfen, ob alle erforderlichen Daten vorhanden sind
    if (!isset($data['customerId'], $data['cardId'], $data['sectionType'], $data['content'])) {
        return $this->json(['message' => 'Fehlende Daten'], 400);
    }

    $customer = $customerRepo->find($data['customerId']);
    if (!$customer) {
        return $this->json(['message' => 'Kunde nicht gefunden'], 404);
    }

    $documentation = $docRepo->findOneBy(['cardId' => $data['cardId'], 'customer' => $customer, 'sectionType' => $data['sectionType']]);

    // Änderungsvalidierung
    if ($documentation && json_encode($documentation->getContent()) === json_encode($data['content'])) {
        return $this->json(['message' => 'Keine Änderungen vorhanden'], 200);
    }

    // Löschlogik, wenn leeren Inhalt erhalten
    if ($documentation && empty($data['content'])) {
        $entityManager->remove($documentation);
        $entityManager->flush();
        return $this->json(['message' => 'Dokumentation erfolgreich gelöscht']);
    }

    // Erstellung oder Aktualisierung der Dokumentation
    if (!$documentation) {
        $documentation = new CustomerDocumentation();
        $documentation->setCreatedAt(new \DateTime());
        $documentation->setCustomer($customer);
        $documentation->setCardId($data['cardId']);
        $documentation->setSectionType($data['sectionType']);
    }

    $documentation->setContent(json_encode($data['content']));
    $documentation->setUpdatedAt(new \DateTime());
    $documentation->setUpdatedBy($this->getUser());

    // Kundenaktualisierungen
    $customer->setUpdatedAt(new \DateTime());
    $customer->setUpdatedBy($this->getUser());

    $entityManager->persist($documentation);
    $entityManager->persist($customer);
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

    #[Route('/user', name: 'user_list')]
    public function userList(UserRepository $userRepository): JsonResponse {
    $users = $userRepository->findAll();
    $userData = array_map(function ($user) {
        return ['id' => $user->getId(), 'username' => $user->getUsername()];
    }, $users);
    return $this->json($userData);
    }


    #[Route('/customer/{id}/print', name: 'customer_print')]
public function printView(int $id, EntityManagerInterface $entityManager, CustomerRepository $customerRepo, CustomerDocumentationRepository $docRepo): Response {
    $customer = $customerRepo->find($id);
    if (!$customer) {
        throw $this->createNotFoundException('Der angefragte Kunde wurde nicht gefunden.');
    }

    $documentationData = [];
    $sectionTypes = ['allgemein', 'netz', 'server', 'clients', 'userpwd', 'routerfirewall', 'provider', 'remotemaintenance', 'backup', 'ups', 'antivirus', 'applicationsoftware', 'otherinfo'];

    foreach ($sectionTypes as $sectionType) {
        $docs = $docRepo->findBy(['customer' => $customer, 'sectionType' => $sectionType]);
        foreach ($docs as $doc) {
            $documentationData[$sectionType][] = [
                'cardId' => $doc->getCardId(),
                'content' => $doc->getContent() ? json_decode($doc->getContent(), true) : [],
                'sectionType' => $doc->getSectionType(),
            ];
        }
    }

    return $this->render('customer/print_view.html.twig', [
        'customer' => $customer,
        'documentationData' => $documentationData,
    ]);
}

}