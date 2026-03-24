<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="invoice_data")
 * @ORM\Entity()
 */
class InvoiceData
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
     * @ORM\Column(type="string", name="street")
     */
    private $street;

    /**
     * @ORM\Column(type="string", name="house_no")
     */
    private $houseNo;

    /**
     * @ORM\Column(type="string", name="apartment_no", nullable=true)
     */
    private $apartmentNo;

    /**
     * @ORM\Column(type="string", name="zip_code")
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", name="city")
     */
    private $city;

    /**
     * @ORM\Column(type="string", name="nip")
     */
    private $nip;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="invoiceData")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getHouseNo(): ?string
    {
        return $this->houseNo;
    }

    public function setHouseNo(string $houseNo): self
    {
        $this->houseNo = $houseNo;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getNip(): ?string
    {
        return $this->nip;
    }

    public function setNip(string $nip): self
    {
        $this->nip = $nip;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getApartmentNo(): ?string
    {
        return $this->apartmentNo;
    }

    public function setApartmentNo(?string $apartmentNo): self
    {
        $this->apartmentNo = $apartmentNo;

        return $this;
    }
}