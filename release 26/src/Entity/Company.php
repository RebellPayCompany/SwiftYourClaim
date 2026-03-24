<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="company")
 * @ORM\Entity()
 */
class Company
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="krs", nullable=true)
     */
    private $krs;

    /**
     * @ORM\Column(type="string", name="nip", nullable=true)
     */
    private $nip;

    /**
     * @ORM\Column(type="string", name="regon", nullable=true)
     */
    private $regon;

    /**
     * @ORM\Column(type="string", name="name", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", name="address", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", name="city", nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", name="phone", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", name="email", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", name="kni", nullable=true)
     */
    private $kni;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="companyIssuer", cascade={"persist", "remove"})
     */
    protected $issuer;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="companyManager", cascade={"persist", "remove"})
     */
    protected $manager;

    /**
     * @ORM\OneToMany(targetEntity="Report", mappedBy="company", cascade={"persist", "remove"})
     */
    protected $report;

    /**
     * @ORM\OneToMany(targetEntity="ReportSummary", mappedBy="company", cascade={"persist", "remove"})
     */
    protected $reportSummary;

    public function __construct()
    {
        $this->issuer = new ArrayCollection();
        $this->manager = new ArrayCollection();
        $this->report = new ArrayCollection();
        $this->reportSummary = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNip(): ?string
    {
        return $this->nip;
    }

    public function setNip(?string $nip): self
    {
        $this->nip = $nip;

        return $this;
    }

    public function getRegon(): ?string
    {
        return $this->regon;
    }

    public function setRegon(?string $regon): self
    {
        $this->regon = $regon;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
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

    /**
     * @return Collection|User[]
     */
    public function getIssuer(): Collection
    {
        return $this->issuer;
    }

    public function addIssuer(User $issuer): self
    {
        if (!$this->issuer->contains($issuer)) {
            $this->issuer[] = $issuer;
            $issuer->setCompanyIssuer($this);
        }

        return $this;
    }

    public function removeIssuer(User $issuer): self
    {
        if ($this->issuer->contains($issuer)) {
            $this->issuer->removeElement($issuer);
            // set the owning side to null (unless already changed)
            if ($issuer->getCompanyIssuer() === $this) {
                $issuer->setCompanyIssuer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getManager(): Collection
    {
        return $this->manager;
    }

    public function addManager(User $manager): self
    {
        if (!$this->manager->contains($manager)) {
            $this->manager[] = $manager;
            $manager->setCompanyManager($this);
        }

        return $this;
    }

    public function removeManager(User $manager): self
    {
        if ($this->manager->contains($manager)) {
            $this->manager->removeElement($manager);
            // set the owning side to null (unless already changed)
            if ($manager->getCompanyManager() === $this) {
                $manager->setCompanyManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Report[]
     */
    public function getReport(): Collection
    {
        return $this->report;
    }

    public function addReport(Report $report): self
    {
        if (!$this->report->contains($report)) {
            $this->report[] = $report;
            $report->setCompany($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->report->contains($report)) {
            $this->report->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getCompany() === $this) {
                $report->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ReportSummary[]
     */
    public function getReportSummary(): Collection
    {
        return $this->reportSummary;
    }

    public function addReportSummary(ReportSummary $reportSummary): self
    {
        if (!$this->reportSummary->contains($reportSummary)) {
            $this->reportSummary[] = $reportSummary;
            $reportSummary->setCompany($this);
        }

        return $this;
    }

    public function removeReportSummary(ReportSummary $reportSummary): self
    {
        if ($this->reportSummary->contains($reportSummary)) {
            $this->reportSummary->removeElement($reportSummary);
            // set the owning side to null (unless already changed)
            if ($reportSummary->getCompany() === $this) {
                $reportSummary->setCompany(null);
            }
        }

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

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
}