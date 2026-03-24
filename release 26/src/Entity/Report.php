<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="report")
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 */
class Report
{
    const STATUS_WAITING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="number", nullable=true)
     */
    private $number;

    /**
     * @ORM\Column(type="date", name="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="integer", name="status", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="boolean", name="active", nullable=true)
     */
    private $active;

    /**
     * @ORM\Column(type="boolean", name="new", nullable=true)
     */
    private $new;

    /**
     * @ORM\Column(type="text", name="description", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text", name="reason_rejection", nullable=true)
     */
    private $reasonRejection;

    /**
     * @ORM\Column(type="boolean", name="no_related_entity", nullable=true)
     */
    private $noRelatedEntity;

    /**
     * @ORM\Column(type="boolean", name="no_related_person", nullable=true)
     */
    private $noRelatedPerson;

    /**
     * @ORM\Column(type="boolean", name="saved", nullable=true)
     */
    private $saved;

    /**
     * @ORM\Column(type="boolean", name="change_krs", nullable=true)
     */
    private $changeKrs;

    /**
     * @ORM\OneToOne(targetEntity="ReportData", mappedBy="report", cascade={"persist", "remove"})
     */
    protected $data;

    /**
     * @ORM\OneToMany(targetEntity="RelatedEntity", mappedBy="report", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $relatedEntity;

    /**
     * @ORM\OneToMany(targetEntity="RelatedPerson", mappedBy="report", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $relatedPerson;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reportManager")
     * @ORM\JoinColumn(name="manager_id", referencedColumnName="id")
     */
    protected $manager;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="report")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    protected $company;

    public function __construct()
    {
        $this->relatedEntity = new ArrayCollection();
        $this->relatedPerson = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;

            $cloneData = clone $this->getData();
            $cloneData->setReport($this);
            $this->data = $cloneData;

            $cloneRelatedEntity = new ArrayCollection();
            foreach ($this->relatedEntity as $item) {
                $itemClone = clone $item;
                $itemClone->setReport($this);
                $cloneRelatedEntity->add($itemClone);
            }
            $this->relatedEntity = $cloneRelatedEntity;

            $cloneRelatedPerson = new ArrayCollection();
            foreach ($this->relatedPerson as $item) {
                $itemClone = clone $item;
                $itemClone->setReport($this);
                $cloneRelatedPerson->add($itemClone);
            }
            $this->relatedPerson = $cloneRelatedPerson;
        }
    }

    public function statusNameArray()
    {
        return [
            self::STATUS_WAITING => 'global.report_status.waiting',
            self::STATUS_APPROVED => 'global.report_status.approved',
            self::STATUS_REJECTED => 'global.report_status.rejected'
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusName()
    {
        $array = $this->statusNameArray();
        return $array[$this->status];
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getReasonRejection(): ?string
    {
        return $this->reasonRejection;
    }

    public function setReasonRejection(string $reasonRejection): self
    {
        $this->reasonRejection = $reasonRejection;

        return $this;
    }

    public function getData(): ?ReportData
    {
        return $this->data;
    }

    public function setData(?ReportData $data): self
    {
        $this->data = $data;

        // set (or unset) the owning side of the relation if necessary
        $newReport = $data === null ? null : $this;
        if ($newReport !== $data->getReport()) {
            $data->setReport($newReport);
        }

        return $this;
    }

    /**
     * @return Collection|RelatedEntity[]
     */
    public function getRelatedEntity(): Collection
    {
        return $this->relatedEntity;
    }

    public function addRelatedEntity(RelatedEntity $relatedEntity): self
    {
        if (!$this->relatedEntity->contains($relatedEntity)) {
            $this->relatedEntity[] = $relatedEntity;
            $relatedEntity->setReport($this);
        }

        return $this;
    }

    public function removeRelatedEntity(RelatedEntity $relatedEntity): self
    {
        if ($this->relatedEntity->contains($relatedEntity)) {
            $this->relatedEntity->removeElement($relatedEntity);
            // set the owning side to null (unless already changed)
            if ($relatedEntity->getReport() === $this) {
                $relatedEntity->setReport(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RelatedPerson[]
     */
    public function getRelatedPerson(): Collection
    {
        return $this->relatedPerson;
    }

    public function addRelatedPerson(RelatedPerson $relatedPerson): self
    {
        if (!$this->relatedPerson->contains($relatedPerson)) {
            $this->relatedPerson[] = $relatedPerson;
            $relatedPerson->setReport($this);
        }

        return $this;
    }

    public function removeRelatedPerson(RelatedPerson $relatedPerson): self
    {
        if ($this->relatedPerson->contains($relatedPerson)) {
            $this->relatedPerson->removeElement($relatedPerson);
            // set the owning side to null (unless already changed)
            if ($relatedPerson->getReport() === $this) {
                $relatedPerson->setReport(null);
            }
        }

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getNoRelatedEntity(): ?bool
    {
        return $this->noRelatedEntity;
    }

    public function setNoRelatedEntity(?bool $noRelatedEntity): self
    {
        $this->noRelatedEntity = $noRelatedEntity;

        return $this;
    }

    public function getNoRelatedPerson(): ?bool
    {
        return $this->noRelatedPerson;
    }

    public function setNoRelatedPerson(?bool $noRelatedPerson): self
    {
        $this->noRelatedPerson = $noRelatedPerson;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getNew(): ?bool
    {
        return $this->new;
    }

    public function setNew(?bool $new): self
    {
        $this->new = $new;

        return $this;
    }

    public function getSaved(): ?bool
    {
        return $this->saved;
    }

    public function setSaved(?bool $saved): self
    {
        $this->saved = $saved;

        return $this;
    }

    public function getChangeKrs(): ?bool
    {
        return $this->changeKrs;
    }

    public function setChangeKrs(?bool $changeKrs): self
    {
        $this->changeKrs = $changeKrs;

        return $this;
    }
}