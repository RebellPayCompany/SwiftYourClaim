<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Ten adres e-mail istnieje już w bazie. Podaj inny.")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", name="token", nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="string", name="first_name", nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", name="last_name", nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="boolean", name="enabled")
     */
    private $enabled;

    /**
     * @ORM\Column(type="boolean", name="deleted")
     */
    private $deleted;

    /**
     * @ORM\Column(type="boolean", name="registered")
     */
    private $registered;

    /**
     * @ORM\Column(type="integer", name="register_step", nullable=true)
     */
    private $registerStep;

    /**
     * @ORM\Column(type="datetime", name="last_login", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="date", name="premium_start", nullable=true)
     */
    private $premiumStart;

    /**
     * @ORM\Column(type="boolean", name="invoice_generate", nullable=true)
     */
    private $invoiceGenerate;

    /**
     * @ORM\OneToOne(targetEntity="UserManager", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $manager;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="issuer")
     * @ORM\JoinColumn(name="company_issuer_id", referencedColumnName="id")
     */
    protected $companyIssuer;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="manager")
     * @ORM\JoinColumn(name="company_manager_id", referencedColumnName="id")
     */
    protected $companyManager;

    /**
     * @ORM\OneToMany(targetEntity="Report", mappedBy="manager", cascade={"persist", "remove"})
     */
    protected $reportManager;

    /**
     * @ORM\OneToMany(targetEntity="Notification", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $notification;

    /**
     * @ORM\OneToMany(targetEntity="ReportSummary", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $reportSummary;

    /**
     * @ORM\OneToMany(targetEntity="UserAccess", mappedBy="manager", cascade={"persist", "remove"})
     */
    protected $accessManager;

    /**
     * @ORM\OneToMany(targetEntity="UserAccess", mappedBy="issuer", cascade={"persist", "remove"})
     */
    protected $accessIssuer;

    /**
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $invoices;

    /**
     * @ORM\OneToOne(targetEntity="InvoiceData", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $invoiceData;

    public function __construct()
    {
        $this->roles = array('ROLE_ISSUER');
        $this->enabled = true;
        $this->deleted = false;
        $this->registered = true;
        $this->reportManager = new ArrayCollection();
        $this->reportIssuer = new ArrayCollection();
        $this->notification = new ArrayCollection();
        $this->reportSummary = new ArrayCollection();
        $this->accessManager = new ArrayCollection();
        $this->accessIssuer = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    // other properties and methods

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getSalt()
    {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function hasRole($str)
    {
        foreach ($this->roles as $role) {
            if ($str == $role) {
                return true;
            }
        }
        return false;
    }

    public function eraseCredentials()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

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

    public function getManager(): ?UserManager
    {
        return $this->manager;
    }

    public function setManager(?UserManager $manager): self
    {
        $this->manager = $manager;

        // set (or unset) the owning side of the relation if necessary
        $newUser = $manager === null ? null : $this;
        if ($newUser !== $manager->getUser()) {
            $manager->setUser($newUser);
        }

        return $this;
    }

    public function getCompanyIssuer(): ?Company
    {
        return $this->companyIssuer;
    }

    public function setCompanyIssuer(?Company $companyIssuer): self
    {
        $this->companyIssuer = $companyIssuer;

        return $this;
    }

    public function getCompanyManager(): ?Company
    {
        return $this->companyManager;
    }

    public function setCompanyManager(?Company $companyManager): self
    {
        $this->companyManager = $companyManager;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getRegisterStep(): ?int
    {
        return $this->registerStep;
    }

    public function setRegisterStep(?int $registerStep): self
    {
        $this->registerStep = $registerStep;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @return Collection|Report[]
     */
    public function getReportManager(): Collection
    {
        return $this->reportManager;
    }

    public function addReportManager(Report $reportManager): self
    {
        if (!$this->reportManager->contains($reportManager)) {
            $this->reportManager[] = $reportManager;
            $reportManager->setManager($this);
        }

        return $this;
    }

    public function removeReportManager(Report $reportManager): self
    {
        if ($this->reportManager->contains($reportManager)) {
            $this->reportManager->removeElement($reportManager);
            // set the owning side to null (unless already changed)
            if ($reportManager->getManager() === $this) {
                $reportManager->setManager(null);
            }
        }

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotification(): Collection
    {
        return $this->notification;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notification->contains($notification)) {
            $this->notification[] = $notification;
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notification->contains($notification)) {
            $this->notification->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            ) = unserialize($serialized, array('allowed_classes' => false));
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getRegistered(): ?bool
    {
        return $this->registered;
    }

    public function setRegistered(bool $registered): self
    {
        $this->registered = $registered;

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
            $reportSummary->setUser($this);
        }

        return $this;
    }

    public function removeReportSummary(ReportSummary $reportSummary): self
    {
        if ($this->reportSummary->contains($reportSummary)) {
            $this->reportSummary->removeElement($reportSummary);
            // set the owning side to null (unless already changed)
            if ($reportSummary->getUser() === $this) {
                $reportSummary->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserAccess[]
     */
    public function getAccessManager(): Collection
    {
        return $this->accessManager;
    }

    public function addAccessManager(UserAccess $accessManager): self
    {
        if (!$this->accessManager->contains($accessManager)) {
            $this->accessManager[] = $accessManager;
            $accessManager->setManager($this);
        }

        return $this;
    }

    public function removeAccessManager(UserAccess $accessManager): self
    {
        if ($this->accessManager->contains($accessManager)) {
            $this->accessManager->removeElement($accessManager);
            // set the owning side to null (unless already changed)
            if ($accessManager->getManager() === $this) {
                $accessManager->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserAccess[]
     */
    public function getAccessIssuer(): Collection
    {
        return $this->accessIssuer;
    }

    public function addAccessIssuer(UserAccess $accessIssuer): self
    {
        if (!$this->accessIssuer->contains($accessIssuer)) {
            $this->accessIssuer[] = $accessIssuer;
            $accessIssuer->setIssuer($this);
        }

        return $this;
    }

    public function removeAccessIssuer(UserAccess $accessIssuer): self
    {
        if ($this->accessIssuer->contains($accessIssuer)) {
            $this->accessIssuer->removeElement($accessIssuer);
            // set the owning side to null (unless already changed)
            if ($accessIssuer->getIssuer() === $this) {
                $accessIssuer->setIssuer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setUser($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getUser() === $this) {
                $invoice->setUser(null);
            }
        }

        return $this;
    }

    public function getInvoiceData(): ?InvoiceData
    {
        return $this->invoiceData;
    }

    public function setInvoiceData(?InvoiceData $invoiceData): self
    {
        $this->invoiceData = $invoiceData;

        // set (or unset) the owning side of the relation if necessary
        $newUser = $invoiceData === null ? null : $this;
        if ($newUser !== $invoiceData->getUser()) {
            $invoiceData->setUser($newUser);
        }

        return $this;
    }

    public function getPremiumStart(): ?\DateTimeInterface
    {
        return $this->premiumStart;
    }

    public function setPremiumStart(?\DateTimeInterface $premiumStart): self
    {
        $this->premiumStart = $premiumStart;

        return $this;
    }

    public function getInvoiceGenerate(): ?bool
    {
        return $this->invoiceGenerate;
    }

    public function setInvoiceGenerate(?bool $invoiceGenerate): self
    {
        $this->invoiceGenerate = $invoiceGenerate;

        return $this;
    }
}