<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Repository\TripRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TripController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(TripRepository $trips): Response
    {
        return $this->render('trip/index.html.twig', [
            'trips' => $trips->findAll(),
        ]);
    }

    #[Route('/trip/{slug}', name: 'trip_show')]
    public function show(Trip $trip): Response {
        return $this->render('trip/show.html.twig', [
            'trip' => $trip,
        ]);
    }
}
