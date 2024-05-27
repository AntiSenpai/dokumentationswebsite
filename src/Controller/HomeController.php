<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class HomeController extends AbstractController
{
    #[Route("/", name: "home")]
    #[IsGranted("ROLE_PRAKTIKANT")]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
