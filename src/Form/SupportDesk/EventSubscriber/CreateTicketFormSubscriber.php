<?php

namespace App\Form\SupportDesk\EventSubscriber;

use App\SupportDesk\Application\Ticket\CreateTicketInput;
use App\SupportDesk\Model\TicketPriority;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class CreateTicketFormSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPreSetData(PreSetDataEvent $event): void
    {
        $data = $event->getData();

        if (!$data instanceof CreateTicketInput) {
            return;
        }

        if ($data->priority === TicketPriority::Urgent) {
            $this->addEscalationReason($event->getForm());
        }
    }

    public function onPreSubmit(PreSubmitEvent $event): void
    {
        $submittedData = $event->getData();

        if (!is_array($submittedData)) {
            return;
        }

        $submittedPriority = $submittedData['priority'] ?? null;

        if ($submittedPriority === TicketPriority::Urgent->value) {
            $this->addEscalationReason($event->getForm());

            return;
        }

        unset($submittedData['escalationReason']);

        $event->setData($submittedData);
    }

    public function onPostSubmit(PostSubmitEvent $event): void
    {
        $form = $event->getForm();

        /*
         * POST_SUBMIT transporte conceptuellement la view data.
         * Pour demander explicitement la model data, on interroge le form.
         */
        $data = $form->getData();

        if (!$data instanceof CreateTicketInput) {
            return;
        }

        if (!$form->has('escalationReason')) {
            return;
        }

        if (trim((string) $data->escalationReason) !== '') {
            return;
        }

        $form
            ->get('escalationReason')
            ->addError(new FormError('Le motif d\'escalade est obligatoire pour une priorité urgente.'));
    }

    private function addEscalationReason(FormInterface $form): void
    {
        if ($form->has('escalationReason')) {
            return;
        }

        $form->add('escalationReason', TextareaType::class, [
            'label' => 'Motif d\'escalade',
            'required' => true,
            'help' => 'Obligatoire uniquement pour une priorité urgente.',
            'attr' => [
                'rows' => 4,
            ],
        ]);
    }
}
