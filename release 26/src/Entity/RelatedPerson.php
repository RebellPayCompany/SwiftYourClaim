<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="related_person")
 * @ORM\Entity()
 */
class RelatedPerson
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
     * @ORM\Column(type="string", name="type_opoz_related", nullable=true)
     */
    private $typeOpozRelated;

    /**
     * @ORM\Column(type="string", name="email", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", name="phone", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="date", name="status_date")
     */
    private $statusDate;

    /**
     * @ORM\Column(type="string", name="document", nullable=true)
     */
    private $document;

    /**
     * @ORM\ManyToOne(targetEntity="Report", inversedBy="relatedPerson")
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

    public function getTypeOpozRelated(): ?string
    {
        return $this->typeOpozRelated;
    }

    public function setTypeOpozRelated(?string $typeOpozRelated): self
    {
        $this->typeOpozRelated = $typeOpozRelated;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getStatusDate(): ?\DateTimeInterface
    {
        return $this->statusDate;
    }

    public function setStatusDate(\DateTimeInterface $statusDate): self
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

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): self
    {
        $this->report = $report;

        return $this;
    }
}