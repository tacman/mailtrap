<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class EmailDebugController extends AbstractController
{
    #[Route('/email/{template}')]
    public function bookings($template, MailerInterface $mailer): Response
    {
        $html = $this->renderView(sprintf('email/%s.html.twig', $template));

        $email = (new Email())
        ->from('hello@example.com')
        ->to('you@example.com')
        ->subject('Time for Symfony Mailer!')
        ->html($html);

    $mailer->send($email);

        return new Response($html);
    }
}
