<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingController extends AbstractController
{
    #[Route('/bookings', name: 'bookings')]
    public function bookings(): Response
    {
        return $this->render('booking/index.html.twig');
    }

    #[Route('/booking', name: 'booking_show')]
    public function show(): Response
    {
        return $this->render('booking/show.html.twig');
    }

    #[Route('/confirm-booking-email')]
    public function _email(): Response
    {
        return $this->render('email/confirm_booking.html.twig');
    }
}
