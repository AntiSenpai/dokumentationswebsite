<?php

namespace App\Controller;

use App\Repository\CustomerDocumentationRepository;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted('ROLE_PRAKTIKANT')]
    public function detail(int $id, LocationRepository $locationRepo): Response {
    $location = $locationRepo->find($id);
    
    if (!$location) {
        throw $this->createNotFoundException('Der Standort konnte nicht gefunden werden.');
    }

    
    $customer = $location->getCustomer();
    $mainLocation = null;
    $subLocations = [];
    
    if ($location->isIstHauptstandort()) {
       
        $mainLocation = $location;
        $subLocations = $locationRepo->findBy(['customer' => $customer, 'istHauptstandort' => false]);
    } else {
      
        $mainLocation = $locationRepo->findOneBy(['customer' => $customer, 'istHauptstandort' => true]);
       
        $subLocations = $locationRepo->findBy(['customer' => $customer, 'istHauptstandort' => false, 'id' => ['!=', $id]]);
    }

   

    return $this->render('location/detail.html.twig', [
        'location' => $location,
        'mainLocation' => $mainLocation,
        'subLocations' => $subLocations,

    ]);
    }



}
