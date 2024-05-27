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
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CustomerDocumentationController extends AbstractController
{

    private $entityManager;
    private $customerDocumentationRepository;

    #[Route('/save-card', name: 'save_card_data', methods: ['POST'])]
    #[IsGranted('ROLE_MITARBEITER')]
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

    #[Route('upload/{cardId}', name: 'file_upload')]
    #[IsGranted('ROLE_MITARBEITER')]
    public function upload(Request $request, $cardName): Response {
        $files = $request->files->get('files');
        $uploadDirectory = $this->getParameter('kernel.project_dir') . "/public/customerFiles/$cardName";

        if(!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        foreach($files as $file) {
            $fileName = $file->getClientOriginalName();
            $file->move($uploadDirectory, $fileName);
        }

        return $this->json(['success' => true, 'files' => array_map(fn($file) => ['name' => $file->getClientOriginalName(), 'url' => "/customerFiles/$cardName/" . $file->getClientOriginalName()], $files)]);
    }
   
}
