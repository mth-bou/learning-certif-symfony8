<?php

namespace App\SupportDesk\Model;

final readonly class Ticket
{
    public function __construct(
        public string $reference,
        public string $title,
        public string $customerEmail,
        public TicketStatus $status,
        public string $description = '',
        public ?string $relatedTicketReference = null,
        public ?TicketAttachment $attachment = null,
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'title' => $this->title,
            'customer_email' => $this->customerEmail,
            'status' => $this->status->value,
            'description' => $this->description,
            'related_ticket_reference' => $this->relatedTicketReference,
            'attachment' => $this->attachment?->toArray(),
        ];
    }
}
