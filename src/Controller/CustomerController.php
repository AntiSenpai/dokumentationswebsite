<?php

namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Customer;
use App\Entity\GeneralInfo;
use App\Form\CustomerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractController
{
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

    #[Route('/create', name:'customer_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($customer);
            $entityManager->flush();

            $this->addFlash('success','Neuer Kunde erfolgreich hinzugefügt.');

            return $this->redirectToRoute('customer_index');
        } 

        return $this->render('customer/create.html.twig', [
        'form'=> $form->createView(),
        ]);
    }

    #[Route('/customer/{id}', name: 'customer_detail')]
    public function detail(int $id, EntityManagerInterface $entityManager): Response {
       
        $customer = $entityManager->getRepository(Customer::class)->find($id);
    
        $generalInfo = $entityManager->getRepository(GeneralInfo::class)->findOneBy(['customer' => $customer]);
 
        if (!$customer) {
            throw $this->createNotFoundException('Content not found');
        }
    

        return $this->render('customer/detail.html.twig', [
            'customer' => $customer,
            'generalinfo' => $generalInfo,
        ]);
    }
    

    #[Route('/delete/{id}', name: 'customer_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, Customer $customer): Response
    {
    if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->request->get('_token'))) {
        $entityManager->remove($customer);
        $entityManager->flush();
    }

    return $this->redirectToRoute('customer_index');
    }

    #[Route('/submit-customer', name: 'submit_customer')]
    public function submitCustomer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($customer);
            $entityManager->flush();
    
            $this->addFlash('success', 'Kunde erfolgreich gespeichert!');
            return $this->redirectToRoute('customer_index');
        }
    
        return $this->render('customer/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }    

}