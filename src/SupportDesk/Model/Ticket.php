<?php

namespace App\SupportDesk\Model;

final readonly class Ticket
{
    public function __construct(
        public string $reference,
        public string $title,
        public string $customerEmail,
        public TicketStatus $status,
    ) {}

    public function toArray(): array
    {
        return [
            'reference' => $this->reference,
            'title' => $this->title,
            'customer_email' => $this->customerEmail,
            'status' => $this->status->value,
        ];
    }
}
