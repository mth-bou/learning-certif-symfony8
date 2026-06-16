<?php

namespace App\SupportDesk\Application\Ticket;

use App\SupportDesk\Model\Ticket;
use App\SupportDesk\Model\TicketStatus;

class TicketProvider
{
    /**
     * @return list<Ticket>
     */
    public function all(): array
    {
        return [
            new Ticket(
                reference: 'TCK-0001',
                title: 'Impossible de se connecter',
                customerEmail: 'alice@example.fr',
                status: TicketStatus::Open,
            ),
            new Ticket(
                reference: 'TCK-0002',
                title: 'Erreur lors du paiement',
                customerEmail: 'bob@example.fr',
                status: TicketStatus::Closed,
            ),
            new Ticket(
                reference: 'TCK-0003',
                title: 'Demande de clôture de compte',
                customerEmail: 'charlie@example.fr',
                status: TicketStatus::Closed,
            ),
        ];
    }

    public function find(string $reference): ?Ticket
    {
        foreach ($this->all() as $ticket) {
            if ($ticket->reference === $reference) {
                return $ticket;
            }
        }

        return null;
    }
}
