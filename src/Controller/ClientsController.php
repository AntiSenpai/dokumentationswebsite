<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Clients;
use App\Form\ClientsType;
use Symfony\Component\HttpFoundation\Request;

class ClientsController extends AbstractController
{
    #[Route('/client/{id}', name: 'client_profile')]
    public function index(Request $request, Clients $client): Response
    {
        $form = $this->createForm(ClientsType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();

            // Rückmeldung an den Benutzer
        }

        return $this->render('client/index.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }
}
