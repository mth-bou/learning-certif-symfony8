<?php

namespace App\Form\SupportDesk;

use App\Form\SupportDesk\DataTransformer\TicketToReferenceTransformer;
use App\SupportDesk\Application\Ticket\CreateTicketInput;
use App\SupportDesk\Model\TicketPriority;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateTicketType extends AbstractType
{
    public function __construct(
        private readonly TicketToReferenceTransformer $ticketToReferenceTransformer,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $addEscalationReason = static function (FormInterface $form, bool $required): void {
            $form->add('escalationReason', TextareaType::class, [
                'label' => 'Motif d\'escalade',
                'required' => $required,
                'help' => 'Obligatoire uniquement pour une priorité urgente.',
                'attr' => [
                    'rows' => 4,
                ]
            ]);
        };

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
            ->add('priority', EnumType::class, [
                'class' => TicketPriority::class,
                'label' => 'Priorité',
                'choice_label' => static fn (TicketPriority $priority): string => $priority->label(),
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($addEscalationReason): void {
            $data = $event->getData();

            if (!$data instanceof CreateTicketInput) {
                return;
            }

            if ($data->priority === TicketPriority::Urgent) {
                $addEscalationReason($event->getForm(), true);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) use ($addEscalationReason): void {
            $submittedData = $event->getData();

            if (!is_array($submittedData)) {
                return;
            }

            if (($submittedData['priority'] ?? null) === TicketPriority::Urgent->value) {
                $addEscalationReason($event->getForm(), true);

                return;
            }

            unset($submittedData['escalationReason']);

            $event->setData($submittedData);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data instanceof CreateTicketInput) {
                return;
            }

            if ($data->priority !== TicketPriority::Urgent) {
                return;
            }

            if (!$form->has('escalationReason')) {
                return;
            }

            if (trim((string) $data->escalationReason) === '') {
                $form
                    ->get('escalationReason')
                    ->addError(new FormError('Le motif d\'escalade est obligatoire pour une priorité urgente.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateTicketInput::class,
        ]);
    }
}
