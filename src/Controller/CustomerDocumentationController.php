<?php

namespace App\Controller;

use App\Entity\CustomerDocumentation;
use App\Repository\CustomerDocumentationRepository;
use App\Repository\CustomerRepository;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerDocumentationController extends AbstractController
{

    private $entityManager;
    private $customerDocumentationRepository;

    #[Route('/save-card', name: 'save_card_data', methods: ['POST'])]
    public function saveCardData(Request $request, EntityManagerInterface $entityManager, CustomerRepository $customerRepository): Response {
        $data = json_decode($request->getContent(), true);

        $customer = $customerRepository->find($data['customerId']);
        if(!$customer) {
            return new Response(json_encode(['status' => 'error', 'message' => 'Kunde nicht gefunden!']), 404, ['Content-Type' => 'application/json']);
        }

        $customerDocumentation = new CustomerDocumentation();
        $customerDocumentation->setCustomer($customer);
        $customerDocumentation->setUpdatedBy($this->getUser());
        $customerDocumentation->setContent(json_encode($data['content']));
        $customerDocumentation->setCardId($data['cardId']);
        $customerDocumentation->setSectionType($data['sectionType']);
        $customerDocumentation->setCreatedAt(new \DateTime());
        $customerDocumentation->setUpdatedAt(new \DateTime());

        $entityManager->persist($customerDocumentation);
        $entityManager->flush();

        return new Response(json_encode(['status' => 'success', 'message' => 'Daten erfolgreich gespeichert!']), 200, ['Content-Type' => 'application/json']);
    }
   
}
