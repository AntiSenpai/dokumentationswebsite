<?php

namespace App\Controller;

use App\Repository\CustomerDocumentationRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    #[Route('/location/entry', name: 'app_location_entry')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/LocationEntryController.php',
        ]);
    }

    #[Route('/location/{id}', name: 'location_detail')]
    public function detail(int $id, LocationRepository $locationRepo, CustomerDocumentationRepository $docRepo, Request $request): Response {
        $location = $locationRepo->find($id);
        if(!$location) {
            throw $this->createNotFoundException('Der Standort konnte nicht gefunden werden.');
        }

        $documentation = $docRepo->findBy(['location' => $location]);

        $customer = $location->getCustomer();
        $mainLocation = $locationRepo->findOneBy(['customer' => $customer, 'istHauptstandort' => true]);
        $subLocation = $locationRepo->findBy(['customer' => $customer, 'istHauptstandort' => false]);

        return $this->render('customer/detail.html.twig', [
            'customer' => $customer,
            'locations' => $location,
            'mainLocation' => $mainLocation,
            'subLocation' => $subLocation,
            'documentation' => $documentation,
            'description' => $location->getDescription(),
        ]);
    }

}
