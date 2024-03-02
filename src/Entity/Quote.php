<?php

namespace App\Entity;

use App\Repository\QuoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
class Quote
{
    use Traits\Timestampable;

    public const DRAFT = "brouillon";
    public const IN_PROGRESS = 'en cours';
    public const WAITING_FOR_DOWNPAYMENT = "en attente du paiement de l'accompte";
    public const VALIDATED = 'validé';
    public const CANCELED = 'annulé';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $status = self::DRAFT;

    #[ORM\Column(nullable: true)]
    private ?int $downPayment = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'json')]
    private array $productsInfo = [];

    #[ORM\Column(nullable: true)]
    private ?int $createdBy = null;

    #[ORM\Column(nullable: true)]
    private ?int $updatedBy = null;

    #[ORM\Column]
    private ?bool $isDeleted = false;

    #[ORM\ManyToOne(inversedBy: 'quotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'quotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: Bill::class)]
    private Collection $bills;

    #[ORM\Column(type: Types::GUID)]
    private ?string $guid = null;

    #[ORM\Column]
    private ?float $tva = null;

    public function __construct()
    {
        $this->guid = Uuid::uuid4()->toString();
        $this->notifications = new ArrayCollection();
        $this->bills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        if (!in_array($status, array(self::DRAFT, self::IN_PROGRESS, self::WAITING_FOR_DOWNPAYMENT, self::VALIDATED, self::CANCELED))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;
    }

    public function getDownPayment(): ?int
    {
        return $this->downPayment;
    }

    public function setDownPayment(int $downPayment): static
    {
        $this->downPayment = $downPayment;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?int $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setQuote($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getQuote() === $this) {
                $notification->setQuote(null);
            }
        }

        return $this;
    }

    public function getProductsInfo(): array
    {
        return $this->productsInfo;
    }

    public function setProductsInfo(array $productsInfo): static
    {
        $this->productsInfo = $productsInfo;

        return $this;
    }

    /**
     * @return Collection<int, Bill>
     */
    public function getBills(): Collection
    {
        return $this->bills;
    }

    public function addBill(Bill $bill): static
    {
        if (!$this->bills->contains($bill)) {
            $this->bills->add($bill);
            $bill->setQuote($this);
        }

        return $this;
    }

    public function removeBill(Bill $bill): static
    {
        if ($this->bills->removeElement($bill)) {
            // set the owning side to null (unless already changed)
            if ($bill->getQuote() === $this) {
                $bill->setQuote(null);
            }
        }

        return $this;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): static
    {
        $this->guid = $guid;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(float $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

    public function getQuoteDetails()
    {
        $quoteTva = $this->getTva();
        $htAmount = 0;
        $tvaAmount = 0;
        $totalAmount = 0;

        foreach ($this->getProductsInfo() as $product) {
            $htAmount += $product['amount'];
            $tvaAmount += $product['amount'] * ($quoteTva / 100);
            $totalAmount += $htAmount + $tvaAmount;
        }

        return [
            'quoteTva' => $quoteTva,
            'htAmount' => $htAmount,
            'tvaAmount' => $tvaAmount,
            'totalAmount' => $totalAmount
        ];
    }
}
