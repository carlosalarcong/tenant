<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\EmergencyAdmissionComplementDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * EmergencyAdmissionComplementDetail (DatoIngresoComplementoUrgenciaDetalle)
 *
 * Legacy table: dato_ingreso_complemento_urgencia_detalle
 * Spanish name: Detalle Complemento Urgencia
 *
 * Detail lines for emergency admission complement: articles, packages
 * or clinical actions ordered/performed during the emergency visit.
 */
#[ORM\Entity(repositoryClass: EmergencyAdmissionComplementDetailRepository::class)]
#[ORM\Table(name: 'emergency_admission_complement_detail')]
#[ORM\HasLifecycleCallbacks]
class EmergencyAdmissionComplementDetail
{
    public const STATUS_PENDING     = 0;
    public const STATUS_DONE        = 1;
    public const STATUS_NOT_DONE    = 2;
    public const STATUS_DELETED     = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: ID_DATO_INGRESO_COMPLEMENTO_URGENCIA */
    #[ORM\ManyToOne(targetEntity: EmergencyAdmissionComplement::class, inversedBy: 'details')]
    #[ORM\JoinColumn(name: 'emergency_admission_complement_id', referencedColumnName: 'id', nullable: false)]
    private ?EmergencyAdmissionComplement $emergencyAdmissionComplement = null;

    /** Legacy: ID_ARTICULO */
    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', nullable: true)]
    private ?Article $article = null;

    /** Legacy: ID_PAQUETE_ARTICULO */
    #[ORM\ManyToOne(targetEntity: ArticlePackage::class)]
    #[ORM\JoinColumn(name: 'article_package_id', referencedColumnName: 'id', nullable: true)]
    private ?ArticlePackage $articlePackage = null;

    /** Legacy: ID_PAQUETE_PRESTACION */
    #[ORM\ManyToOne(targetEntity: ServicePackage::class)]
    #[ORM\JoinColumn(name: 'service_package_id', referencedColumnName: 'id', nullable: true)]
    private ?ServicePackage $servicePackage = null;

    /**
     * Legacy: ESTADO (0=Pendiente, 1=Realizado, 2=No Realizado, 3=Eliminado)
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $status = self::STATUS_PENDING;

    /** Legacy: FECHA_INGRESO */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $entryDate = null;

    /** Legacy: ES_PAQUETE */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPackage = false;

    /** Legacy: CANTIDADARTICULO */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $articleQuantity = null;

    /** Legacy: COMENTARIO */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    /** Legacy: FECHA_COMENTARIO */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $commentDate = null;

    /** Legacy: FECHA_ACTUALIZACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmergencyAdmissionComplement(): ?EmergencyAdmissionComplement
    {
        return $this->emergencyAdmissionComplement;
    }

    public function setEmergencyAdmissionComplement(?EmergencyAdmissionComplement $emergencyAdmissionComplement): self
    {
        $this->emergencyAdmissionComplement = $emergencyAdmissionComplement;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function getArticlePackage(): ?ArticlePackage
    {
        return $this->articlePackage;
    }

    public function setArticlePackage(?ArticlePackage $articlePackage): self
    {
        $this->articlePackage = $articlePackage;
        return $this;
    }

    public function getServicePackage(): ?ServicePackage
    {
        return $this->servicePackage;
    }

    public function setServicePackage(?ServicePackage $servicePackage): self
    {
        $this->servicePackage = $servicePackage;
        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'Pendiente',
            self::STATUS_DONE     => 'Realizado',
            self::STATUS_NOT_DONE => 'No Realizado',
            self::STATUS_DELETED  => 'Eliminado',
            default               => 'Desconocido',
        };
    }

    public function getEntryDate(): ?\DateTimeInterface
    {
        return $this->entryDate;
    }

    public function setEntryDate(?\DateTimeInterface $entryDate): self
    {
        $this->entryDate = $entryDate;
        return $this;
    }

    public function isPackage(): bool
    {
        return $this->isPackage;
    }

    public function setIsPackage(bool $isPackage): self
    {
        $this->isPackage = $isPackage;
        return $this;
    }

    public function getArticleQuantity(): ?int
    {
        return $this->articleQuantity;
    }

    public function setArticleQuantity(?int $articleQuantity): self
    {
        $this->articleQuantity = $articleQuantity;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCommentDate(): ?\DateTimeInterface
    {
        return $this->commentDate;
    }

    public function setCommentDate(?\DateTimeInterface $commentDate): self
    {
        $this->commentDate = $commentDate;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
