<?php

namespace App\Controller;

use App\Entity\GeneralInfo;
use App\Form\GeneralType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GeneralInfoController extends AbstractController
{
    public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
{
    $generalInfo = $entityManager->getRepository(GeneralInfo::class)->find($id);

    if (!$generalInfo) {
        throw $this->createNotFoundException('Keine ID gefunden für diese ID '.$id);
    }

    $form = $this->createForm(GeneralInfo::class, $generalInfo);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($generalInfo);
        $entityManager->flush();

        $this->addFlash('success', 'Änderungen gespeichert.');

        return $this->redirectToRoute('some_route'); // Replace 'some_route' with the appropriate route
    }

    return $this->render('general_info/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

public function ajaxEdit(Request $request, EntityManagerInterface $entityManager, $id): Response
{
    $generalInfo = $entityManager->getRepository(GeneralInfo::class)->find($id);

    if (!$generalInfo) {
        return new Response('Entity not found', 404);
    }

    $form = $this->createForm(GeneralInfo::class, $generalInfo);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($generalInfo);
        $entityManager->flush();

        return new Response('Success');
    }

    return $this->render('form/general_info_form.html.twig', [
        'form' => $form->createView(),
    ]);
}


}
