<?php

namespace App\Form\SupportDesk;

use App\Form\SupportDesk\DataTransformer\TicketToReferenceTransformer;
use App\SupportDesk\Application\Ticket\CreateTicketInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateTicketType extends AbstractType
{
    public function __construct(
        private readonly TicketToReferenceTransformer $ticketToReferenceTransformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('customerEmail', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 8,
                ]
            ])
            ->add('relatedTicket', TextType::class, [
                'label' => 'Ticket lié',
                'required' => false,
                'help' => 'Exemple : TCK-0001',
                'invalid_message' => 'Aucun ticket ne correspond à cette référence.',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer le ticket',
            ]);

        $builder
            ->get('relatedTicket')
            ->addModelTransformer($this->ticketToReferenceTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateTicketInput::class,
        ]);
    }
}
