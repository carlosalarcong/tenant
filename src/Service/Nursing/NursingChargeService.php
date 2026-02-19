<?php

namespace App\Service\Nursing;

use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\NursingCharge;
use App\Repository\Tenant\NursingChargeRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class NursingChargeService
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private NursingChargeRepository $nursingChargeRepository
    ) {}

    public function chargeInterventions(AdmissionRecord $record, array $items): void
    {
        foreach ($items as $item) {
            $this->createCharge($record, 'intervention', (int) ($item['id'] ?? 0), $item);
        }

        $this->entityManager->flush();
    }

    public function chargeArticles(AdmissionRecord $record, array $items): void
    {
        foreach ($items as $item) {
            $this->createCharge($record, 'article', (int) ($item['id'] ?? 0), $item);
        }

        $this->entityManager->flush();
    }

    public function chargePackages(AdmissionRecord $record, array $packages): void
    {
        foreach ($packages as $package) {
            $this->createCharge($record, 'package', (int) ($package['id'] ?? 0), $package);
        }

        $this->entityManager->flush();
    }

    public function cancelCharge(int $chargeId, string $type): void
    {
        $charge = $this->nursingChargeRepository->find($chargeId);
        if (!$charge instanceof NursingCharge || $charge->getChargeType() !== $type) {
            return;
        }

        $charge->setStatus('cancelled');
        $charge->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function rejectCharge(int $chargeId, string $type): void
    {
        $charge = $this->nursingChargeRepository->find($chargeId);
        if (!$charge instanceof NursingCharge || $charge->getChargeType() !== $type) {
            return;
        }

        $charge->setStatus('rejected');
        $charge->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    private function createCharge(AdmissionRecord $record, string $type, int $referenceId, array $payload): void
    {
        $charge = (new NursingCharge())
            ->setAdmissionRecord($record)
            ->setChargeType($type)
            ->setReferenceId($referenceId > 0 ? $referenceId : null)
            ->setPayload($payload)
            ->setStatus('active');

        $this->entityManager->persist($charge);
    }
}
