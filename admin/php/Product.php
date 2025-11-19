<?php

class Product
{
    private int $productId;
    private string $productName;
    private int $categoryId;
    private float $price;
    private string $description;
    private string $imageURL;
    private string $status;
    private int $supplierId;
    private int $quantityInStock;

    public function __construct(
        int $productId,
        string $productName,
        int $categoryId,
        float $price,
        string $description,
        string $imageURL,
        string $status,
        int $supplierId,
        int $quantityInStock
    ) {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->categoryId = $categoryId;
        $this->price = $price;
        $this->description = $description;
        $this->imageURL = $imageURL;
        $this->status = $status;
        $this->supplierId = $supplierId;
        $this->quantityInStock = $quantityInStock;
    }

    // GETTERS
    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImageURL(): string
    {
        return $this->imageURL;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSupplierId(): int
    {
        return $this->supplierId;
    }

    public function getQuantityInStock(): int
    {
        return $this->quantityInStock;
    }

    // Convert object to array (useful for JSON or DB operations)
    public function toArray(): array
    {
        return [
            'ProductID' => $this->productId,
            'ProductName' => $this->productName,
            'CategoryID' => $this->categoryId,
            'Price' => $this->price,
            'Description' => $this->description,
            'ImageURL' => $this->imageURL,
            'Status' => $this->status,
            'Supplier_id' => $this->supplierId,
            'quantity_in_stock' => $this->quantityInStock
        ];
    }
}
