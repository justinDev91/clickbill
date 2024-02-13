<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already a client with this email')]
class Client
{
    use Traits\Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Assert\NotNull]
    #[Assert\NotBlank(message: "First name cannot be blank.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "First name should be at least {{ limit }} characters long.",
        maxMessage: "First name should not be longer than {{ limit }} characters."
    )]
    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[Assert\NotNull]
    #[Assert\NotBlank(message: "Last name cannot be blank.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Last name should be at least {{ limit }} characters long.",
        maxMessage: "Last name should not be longer than {{ limit }} characters."
    )]
    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['firstName', 'lastName'])]
    private $slug;

    #[Assert\NotNull]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Assert\NotNull]
    #[Assert\NotBlank(message: "Phone cannot be blank.")]
    #[Assert\Length(
        min: 10,
        max: 20,
        minMessage: "Phone should be at least {{ limit }} characters long.",
        maxMessage: "Phone should not be longer than {{ limit }} characters."
    )]
    #[Assert\Regex(
        pattern: "/^\+?[0-9]+$/",
        message: "Invalid characters in Phone. Only numeric characters are allowed."
    )]
    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[Assert\NotNull]
    #[Assert\NotBlank(message: "Address cannot be blank.")]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: "Address should be at least {{ limit }} characters long.",
        maxMessage: "Address should not be longer than {{ limit }} characters."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\-\' ]+$/",
        message: "Invalid characters in address. Only letters, numbers, and spaces are allowed."
    )]
    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column]
    private ?int $createdBy = null;

    #[ORM\Column(nullable: true)]
    private ?int $updatedBy = null;

    #[ORM\Column]
    private ?bool $isDeleted = false;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Bill::class)]
    private Collection $bills;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Quote::class)]
    private Collection $quotes;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: ClientInteraction::class)]
    private Collection $clientInteractions;

    public function __construct()
    {
        $this->bills = new ArrayCollection();
        $this->quotes = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->clientInteractions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     * @return Client
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }


    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        $this->displayName = $this->firstName . ' ' . $this->lastName;

        return $this->displayName;
    }

    public function __toString()
    {
        return $this->firstName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

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
            $bill->setClient($this);
        }

        return $this;
    }

    public function removeBill(Bill $bill): static
    {
        if ($this->bills->removeElement($bill)) {
            // set the owning side to null (unless already changed)
            if ($bill->getClient() === $this) {
                $bill->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Quote>
     */
    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addQuote(Quote $quote): static
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes->add($quote);
            $quote->setClient($this);
        }

        return $this;
    }

    public function removeQuote(Quote $quote): static
    {
        if ($this->quotes->removeElement($quote)) {
            // set the owning side to null (unless already changed)
            if ($quote->getClient() === $this) {
                $quote->setClient(null);
            }
        }

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
            $notification->setClient($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getClient() === $this) {
                $notification->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClientInteraction>
     */
    public function getClientInteractions(): Collection
    {
        return $this->clientInteractions;
    }

    public function addClientInteraction(ClientInteraction $clientInteraction): static
    {
        if (!$this->clientInteractions->contains($clientInteraction)) {
            $this->clientInteractions->add($clientInteraction);
            $clientInteraction->setClient($this);
        }

        return $this;
    }

    public function removeClientInteraction(ClientInteraction $clientInteraction): static
    {
        if ($this->clientInteractions->removeElement($clientInteraction)) {
            // set the owning side to null (unless already changed)
            if ($clientInteraction->getClient() === $this) {
                $clientInteraction->setClient(null);
            }
        }

        return $this;
    }
}
