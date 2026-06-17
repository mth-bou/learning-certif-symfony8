<?php

namespace App\SupportDesk\Application\Ticket;

use App\SupportDesk\Model\Ticket;
use App\SupportDesk\Model\TicketStatus;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class TicketProvider
{
    private const string SESSION_KEY = 'supportdesk.tickets';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @return list<Ticket>
     */
    public function all(): array
    {
        return [
            ...$this->seedTickets(),
            ...$this->sessionTickets(),
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

    public function add(CreateTicketInput $input): Ticket
    {
        $ticket = new Ticket(
            reference: $this->nextReference(),
            title: $input->title,
            customerEmail: $input->customerEmail,
            status: TicketStatus::Open,
            description: $input->description,
            relatedTicketReference: $input->relatedTicket?->reference,
        );

        $session = $this->requestStack->getSession();
        $tickets = $session->get(self::SESSION_KEY, []);
        $tickets[] = $ticket->toArray();

        $session->set(self::SESSION_KEY, $tickets);

        return $ticket;
    }

    public function nextReference(): string
    {
        return sprintf('TCK-%04d', count($this->all()) +1 );
    }

    public function seedTickets(): array
    {
        return [
            new Ticket(
                reference: 'TCK-0001',
                title: 'Impossible de se connecter',
                customerEmail: 'alice@example.fr',
                status: TicketStatus::Open,
                description: 'Alice ne parvient plus à se connecter à son espace client.',
            ),
            new Ticket(
                reference: 'TCK-0002',
                title: 'Erreur lors du paiement',
                customerEmail: 'bob@example.fr',
                status: TicketStatus::Closed,
                description: 'Bob obtient une erreur lors de la validation de son paiement.',
            ),
            new Ticket(
                reference: 'TCK-0003',
                title: 'Demande de clôture de compte',
                customerEmail: 'charlie@example.fr',
                status: TicketStatus::Closed,
                description: 'Charlie souhaite clôturer son compte utilisateur.',
            ),
        ];
    }

    public function sessionTickets(): array
    {
        $session = $this->requestStack->getSession();

        return array_map(
            static fn (array $data): Ticket => new Ticket(
                reference: $data['reference'],
                title: $data['title'],
                customerEmail: $data['customer_email'],
                status: TicketStatus::from($data['status']),
                description: $data['description'] ?? '',
                relatedTicketReference: $data['related_ticket_reference'] ?? null,
            ),
            $session->get(self::SESSION_KEY, []),
        );
    }
}
