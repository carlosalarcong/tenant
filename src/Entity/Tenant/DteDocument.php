<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\DteDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * DteDocument
 *
 * Registro de un documento tributario electrónico (DTE) emitido vía Aces
 * para un PaymentAccount. Una boleta puede ser afecta (tipodte=39) o
 * exenta (tipodte=41). Un mismo PaymentAccount puede generar hasta dos
 * DteDocuments si tiene prestaciones de ambos tipos.
 *
 * Ciclo de vida: sent → (sin cambio) | error → retry_pending → sent | cancelled
 */
#[ORM\Entity(repositoryClass: DteDocumentRepository::class)]
#[ORM\Table(name: 'dte_document')]
class DteDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Pago al que corresponde este DTE.
     */
    #[ORM\ManyToOne(targetEntity: PaymentAccount::class)]
    #[ORM\JoinColumn(name: 'payment_account_id', referencedColumnName: 'id', nullable: false)]
    private ?PaymentAccount $paymentAccount = null;

    /**
     * Tipo de documento tributario: '39' (Boleta Afecta) | '41' (Boleta Exenta).
     */
    #[ORM\Column(length: 5)]
    private string $tipodte = '41';

    /**
     * Folio del talonario interno consumido en este cobro.
     */
    #[ORM\Column(type: 'integer')]
    private int $folioNumber = 0;

    /**
     * Estado del envío: 'sent' | 'error' | 'retry_pending' | 'cancelled'
     */
    #[ORM\Column(length: 30, options: ['default' => 'sent'])]
    private string $status = 'sent';

    /**
     * Respuesta completa de Aces serializada como JSON.
     * Null hasta recibir respuesta.
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $acesResponse = null;

    /**
     * Datos del payload de negocio (sin credenciales) para reintento.
     * Se guarda cuando status = 'error' o 'retry_pending'.
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $retryData = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaymentAccount(): ?PaymentAccount
    {
        return $this->paymentAccount;
    }

    public function setPaymentAccount(?PaymentAccount $paymentAccount): self
    {
        $this->paymentAccount = $paymentAccount;
        return $this;
    }

    public function getTipodte(): string
    {
        return $this->tipodte;
    }

    public function setTipodte(string $tipodte): self
    {
        $this->tipodte = $tipodte;
        return $this;
    }

    public function getFolioNumber(): int
    {
        return $this->folioNumber;
    }

    public function setFolioNumber(int $folioNumber): self
    {
        $this->folioNumber = $folioNumber;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getAcesResponse(): ?array
    {
        return $this->acesResponse;
    }

    public function setAcesResponse(?array $acesResponse): self
    {
        $this->acesResponse = $acesResponse;
        return $this;
    }

    public function getRetryData(): ?array
    {
        return $this->retryData;
    }

    public function setRetryData(?array $retryData): self
    {
        $this->retryData = $retryData;
        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): self
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isRetryable(): bool
    {
        return in_array($this->status, ['error', 'retry_pending'], true);
    }
}
