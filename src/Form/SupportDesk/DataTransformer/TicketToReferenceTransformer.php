<?php

namespace App\Form\SupportDesk\DataTransformer;

use App\SupportDesk\Application\Ticket\TicketProvider;
use App\SupportDesk\Model\Ticket;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class TicketToReferenceTransformer implements DataTransformerInterface
{
    public function __construct(
        private TicketProvider $ticketProvider,
    ) {
    }

    public function transform(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!$value instanceof Ticket) {
            throw new TransformationFailedException(sprintf(
                'Expected "%s" or null, got "%s".',
                Ticket::class,
                get_debug_type($value)
            ));
        }

        return $value->reference;
    }

    public function reverseTransform(mixed $value): ?Ticket
    {
        $reference = trim((string) $value);

        if ($reference === '') {
            return null;
        }

        $ticket = $this->ticketProvider->find($reference);

        if ($ticket === null) {
            throw new TransformationFailedException(sprintf(
                'Ticket "%s" introuvable.',
                $reference
            ));
        }

        return $ticket;
    }
}
