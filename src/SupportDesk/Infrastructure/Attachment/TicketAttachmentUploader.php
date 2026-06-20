<?php

namespace App\SupportDesk\Infrastructure\Attachment;

use App\SupportDesk\Model\TicketAttachment;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class TicketAttachmentUploader
{
    public function __construct(
        private string $targetDirectory,
    ) {}

    public function upload(UploadedFile $file): TicketAttachment
    {
        $this->assertTargetDirectoryIsWritable();

        $extension = $file->guessExtension();

        if ($extension === null) {
            throw new AttachmentUploadFailed(
                'Impossible de déterminer l\'extension du fichier.'
            );
        }

        $attachment = new TicketAttachment(
            storageName: sprintf(
                '%s.%s',
                bin2hex(random_bytes(16)),
                $extension,
            ),
            originalName: $file->getClientOriginalName(),
            mediaType: $file->getMimeType(),
            size: (int) $file->getSize(),
        );

        try {
            $file->move(
                $this->targetDirectory,
                $attachment->storageName,
            );
        } catch (FileException $exception) {
            throw new AttachmentUploadFailed(
                'La pièce jointe n\'a pas pu être déplacée.',
                 previous: $exception,
            );
        }

        return $attachment;
    }

    private function assertTargetDirectoryIsWritable(): void
    {
        if (
            !is_dir($this->targetDirectory)
            && !is_writable($this->targetDirectory)
        ) {
            throw new AttachmentUploadFailed(sprintf(
                'Le répertoire "%s" est absent ou non accessible en écriture.',
                $this->targetDirectory,
            ));
        }
    }
}
