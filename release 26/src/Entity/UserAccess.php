<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="user_access")
 * @ORM\Entity(repositoryClass="App\Repository\UserAccessRepository")
 */
class UserAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="accessManager")
     * @ORM\JoinColumn(name="manager_id", referencedColumnName="id")
     */
    protected $manager;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="accessIssuer")
     * @ORM\JoinColumn(name="issuer_id", referencedColumnName="id")
     */
    protected $issuer;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIssuer(): ?User
    {
        return $this->issuer;
    }

    public function setIssuer(?User $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }
}