<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TripController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('trip/index.html.twig');
    }

    #[Route('/trip', name: 'trip_show')]
    public function show(): Response
    {
        return $this->render('trip/show.html.twig');
    }
}
