<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="related_entity")
 * @ORM\Entity()
 */
class RelatedEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="name")
     */
    private $name;

    /**
     * @ORM\Column(type="string", name="address", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", name="type_otc_related", nullable=true)
     */
    private $typeOtcRelated;

    /**
     * @ORM\Column(type="string", name="phone", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", name="email", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", name="business_address", nullable=true)
     */
    private $businessAddress;

    /**
     * @ORM\Column(type="string", name="krs", nullable=true)
     */
    private $krs;

    /**
     * @ORM\Column(type="string", name="kni", nullable=true)
     */
    private $kni;

    /**
     * @ORM\Column(type="date", name="status_date", nullable=true)
     */
    private $statusDate;

    /**
     * @ORM\Column(type="string", name="document", nullable=true)
     */
    private $document;

    /**
     * @ORM\Column(type="boolean", name="detect", nullable=true)
     */
    private $detect;

    /**
     * @ORM\Column(type="boolean", name="deleted", nullable=true)
     */
    private $deleted;

    /**
     * @ORM\ManyToOne(targetEntity="Report", inversedBy="relatedEntity")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    protected $report;

    public function __construct()
    {
        $this->statusDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getTypeOtcRelated(): ?string
    {
        return $this->typeOtcRelated;
    }

    public function setTypeOtcRelated(?string $typeOtcRelated): self
    {
        $this->typeOtcRelated = $typeOtcRelated;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBusinessAddress(): ?string
    {
        return $this->businessAddress;
    }

    public function setBusinessAddress(?string $businessAddress): self
    {
        $this->businessAddress = $businessAddress;

        return $this;
    }

    public function getKrs(): ?string
    {
        return $this->krs;
    }

    public function setKrs(?string $krs): self
    {
        $this->krs = $krs;

        return $this;
    }

    public function getKni(): ?string
    {
        return $this->kni;
    }

    public function setKni(?string $kni): self
    {
        $this->kni = $kni;

        return $this;
    }

    public function getStatusDate(): ?\DateTimeInterface
    {
        return $this->statusDate;
    }

    public function setStatusDate(?\DateTimeInterface $statusDate): self
    {
        $this->statusDate = $statusDate;

        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getDetect(): ?bool
    {
        return $this->detect;
    }

    public function setDetect(?bool $detect): self
    {
        $this->detect = $detect;

        return $this;
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): self
    {
        $this->report = $report;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
}