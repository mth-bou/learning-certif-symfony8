<?php

namespace App\SupportDesk\Application\Ticket;

use App\SupportDesk\Model\Ticket;
use App\SupportDesk\Model\TicketPriority;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateTicketInput
{
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(
        min: 5,
        max: 120,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le titre ne peut dépasser {{ limit }} caractères.'
    )]
    public ?string $title = null;

    #[Assert\NotBlank(message: 'L\'adresse email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email "{{ value }} n\'est pas valide.')]
    public ?string $customerEmail = null;

    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'La description ne peut dépasser {{ limit }} caractères.'
    )]
    public ?string $description = null;

    public ?Ticket $relatedTicket = null;

    #[Assert\NotNull(message: 'La priorité est obligatoire.')]
    public ?TicketPriority $priority = TicketPriority::Normal;

    public ?string $escalationReason = null;
}
