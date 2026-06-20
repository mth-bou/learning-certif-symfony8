<?php

namespace App\Controller\SupportDesk;

use App\Form\SupportDesk\CreateTicketType;
use App\SupportDesk\Application\Ticket\CreateTicketInput;
use App\SupportDesk\Application\Ticket\TicketProvider;
use App\SupportDesk\Infrastructure\Attachment\AttachmentUploadFailed;
use App\SupportDesk\Infrastructure\Attachment\TicketAttachmentUploader;
use App\SupportDesk\Model\TicketPriority;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/support/tickets', name: 'support_ticket_')]
final class TicketController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(TicketProvider $provider): Response
    {
        return $this->render('support/ticket/index.html.twig', [
            'tickets' => $provider->all()
        ]);
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        TicketProvider $provider,
        TicketAttachmentUploader $attachmentUploader,
    ): Response {
        $input = new CreateTicketInput();

        $form = $this->createForm(CreateTicketType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attachment = null;

            if ($input->attachment !== null) {
                try {
                    $attachment = $attachmentUploader->upload($input->attachment);
                } catch (AttachmentUploadFailed) {
                    $form
                        ->get('attachment')
                        ->addError(new FormError(
                            'La pièce jointe n\'a pas pu être enregistrée.',
                        ))
                    ;

                    return $this->render(
                        'support/ticket/new.html.twig',
                        [
                            'form' => $form,
                        ],
                        new Response(
                            status: Response::HTTP_UNPROCESSABLE_ENTITY,
                        ),
                    );
                }
            }

            $ticket = $provider->add($input, $attachment);

            $this->addFlash('success', sprintf(
                'Le ticket %s a été créé.',
                $ticket->reference,
            ));

            return $this->redirectToRoute('support_ticket_show', [
                'reference' => $ticket->reference,
            ]);
        }

        return $this->render('support/ticket/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{reference}', name: 'show', requirements: ['reference' => 'TCK-\d{4}'], methods: ['GET'])]
    public function show(string $reference, TicketProvider $provider): Response
    {
        $ticket = $provider->find($reference);

        if ($ticket === null) {
            throw $this->createNotFoundException(sprintf(
                'Ticket "%s" introuvable.',
                $reference
            ));
        }

        return $this->render('support/ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }
}
