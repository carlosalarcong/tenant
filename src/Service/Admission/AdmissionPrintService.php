<?php

namespace App\Service\Admission;

use App\Entity\Tenant\AdmissionRecord;

class AdmissionPrintService
{
    public const VALID_TYPES = ['pdf', 'html'];

    public function isValidDocumentType(string $type): bool
    {
        return in_array($type, self::VALID_TYPES, true);
    }

    public function buildAdmissionPrintContext(AdmissionRecord $record, string $type): array
    {
        return [
            'admission_id' => $record->getId(),
            'document_type' => $type,
            'printed_at' => new \DateTimeImmutable(),
        ];
    }
}
