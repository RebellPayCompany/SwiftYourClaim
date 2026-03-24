<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="report_data")
 * @ORM\Entity()
 */
class ReportData
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="pesel", nullable=true)
     */
    private $pesel;

    /**
     * @ORM\Column(type="boolean", name="no_pesel", nullable=true)
     */
    private $noPesel;

    /**
     * @ORM\Column(type="string", name="first_name", nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", name="last_name", nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", name="position", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="string", name="address", nullable=true)
     */
    private $address;

    /**
     * @ORM\OneToOne(targetEntity="Report", inversedBy="data")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    protected $report;

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPesel(): ?string
    {
        return $this->pesel;
    }

    public function setPesel(?string $pesel): self
    {
        $this->pesel = $pesel;

        return $this;
    }

    public function getNoPesel(): ?bool
    {
        return $this->noPesel;
    }

    public function setNoPesel(?bool $noPesel): self
    {
        $this->noPesel = $noPesel;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;

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