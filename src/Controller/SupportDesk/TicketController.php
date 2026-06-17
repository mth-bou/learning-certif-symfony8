<?php

namespace App\Controller\SupportDesk;

use App\SupportDesk\Application\Ticket\TicketProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/support/tickets', name: 'support_ticket_')]
final class TicketController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(TicketProvider $provider): Response
    {
        return $this->render('index.html.twig', [
            'tickets' => $provider->all()
        ]);
    }

    #[Route(path: '/{reference}', name: 'show', requirements: ['reference' => 'TCK-\d{4}'], methods: ['GET'])]
    public function show(string $reference, TicketProvider $provider): Response
    {
        $ticket = $provider->find($reference);

        if ($ticket === null) {
            throw $this->createNotFoundException(sprintf(
                'Ticket "%s" introuvable.',
                $reference
            ));
        }

        return $this->render('show.html.twig', [
            'ticket' => $ticket,
        ]);
    }
}
