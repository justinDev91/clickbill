<?php

namespace App\Entity;

use App\Repository\ProductBillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductBillRepository::class)]
class ProductBill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productBills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $productId = null;

    #[ORM\ManyToOne(inversedBy: 'productBills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bill $billId = null;

    #[ORM\Column]
    private ?float $productPriceAtBillCreation = null;

    #[ORM\Column(length: 255)]
    private ?string $productNameAtBillCreation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $productDescriptionAtBillCreation = null;

    #[ORM\Column]
    private ?int $productQuantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): ?Product
    {
        return $this->productId;
    }

    public function setProductId(?Product $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function getBillId(): ?Bill
    {
        return $this->billId;
    }

    public function setBillId(?Bill $billId): static
    {
        $this->billId = $billId;

        return $this;
    }

    public function getProductPriceAtBillCreation(): ?float
    {
        return $this->productPriceAtBillCreation;
    }

    public function setProductPriceAtBillCreation(float $productPriceAtBillCreation): static
    {
        $this->productPriceAtBillCreation = $productPriceAtBillCreation;

        return $this;
    }

    public function getProductNameAtBillCreation(): ?string
    {
        return $this->productNameAtBillCreation;
    }

    public function setProductNameAtBillCreation(string $productNameAtBillCreation): static
    {
        $this->productNameAtBillCreation = $productNameAtBillCreation;

        return $this;
    }

    public function getProductDescriptionAtBillCreation(): ?string
    {
        return $this->productDescriptionAtBillCreation;
    }

    public function setProductDescriptionAtBillCreation(string $productDescriptionAtBillCreation): static
    {
        $this->productDescriptionAtBillCreation = $productDescriptionAtBillCreation;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->productQuantity;
    }

    public function setQuantity(int $productQuantity): static
    {
        $this->productQuantity = $productQuantity;

        return $this;
    }
}
