<?php
// src/Controller/TimeTrackingController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TimeTrackingController extends AbstractController
{
    /**
    * @Route("/zeiterfassung", name="zeiterfassung")
    */
    public function index(): Response {
        // Hier Logik für Zeiterfassung
        return $this->render('time_tracking/index.html.twig');
    }
}