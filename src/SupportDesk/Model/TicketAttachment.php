<?php

namespace App\SupportDesk\Model;

final readonly class TicketAttachment
{
    public function __construct(
        public string  $storageName,
        public string  $originalName,
        public ?string $mediaType,
        public int     $size,
    )
    {
    }

    /**
     * @return array{
     *     storage_name: string,
     *     original_name: string,
     *     media_type: string|null,
     *     size: int
     * }
     */
    public function toArray(): array
    {
        return [
            'storage_name' => $this->storageName,
            'original_name' => $this->originalName,
            'media_type' => $this->mediaType,
            'size' => $this->size,
        ];
    }

    /**
     * @param array{
     *     storage_name: string,
     *     original_name: string,
     *     media_type?: string|null,
     *     size: int
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            storageName: (string) $data['storage_name'],
            originalName: (string) $data['original_name'],
            mediaType: isset($data['media_type'])
                ? (string) $data['media_type']
                : null,
            size: (int) $data['size'],
        );
    }
}
