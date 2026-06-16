<?php

namespace App\Controller;

use App\SupportDesk\Application\Ticket\TicketProvider;
use App\SupportDesk\Model\Ticket;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/support/tickets', name: 'support_ticket_')]
class TicketController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(TicketProvider $provider): JsonResponse
    {
        return new JsonResponse([
            'tickets' => array_map(
                static fn(Ticket $ticket) => $ticket->toArray(),
                $provider->all(),
            ),
        ]);
    }

    #[Route(path: '/{reference}', name: 'show', requirements: ['reference' => 'TCK-\d{4}'], methods: ['GET'])]
    public function show(string $reference, TicketProvider $provider): JsonResponse
    {
        $ticket = $provider->find($reference);

        if ($ticket === null) {
            throw new NotFoundHttpException(sprintf(
                'Ticket "%s" introuvable.',
                $reference
            ));
        }

        return new JsonResponse([
            'ticket' => $ticket->toArray(),
        ]);
    }
}
