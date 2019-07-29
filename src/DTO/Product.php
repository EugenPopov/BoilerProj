<?php


namespace App\DTO;


use DateTimeInterface;

class Product
{
    private $category_id;
    private $name;
    private $description;
    private $wholesale_price;
    private $retail_price;
    private $created_at;
    private $updated_at;
    private $is_available;
    private $is_visible;
    private $special_offer;
    private $minimum_wholesale;
    private $sale;
    private $product_value;
    private $product_unit;
    private $slug;
    private $image;
    private $currency_name;
    private $brand;
    private $amount;
    private $specifications;
    private $category;
    private $id;
    private $seo_description;

    public function __construct(int $category_id, string $name, float $retail_price, DateTimeInterface $created_at, DateTimeInterface $updated_at, bool $is_available, bool $is_visible, bool $special_offer, string $slug, string $description = null, float $wholesale_price = null, int $minimum_wholesale = null, float $sale = null, float $product_value = null, string $product_unit = null, string $currency_name = null, $brand = null, $image = null, $id = null, $seo_description = null)
    {
        $this->category_id = $category_id;
        $this->name = $name;
        $this->description = $description;
        $this->wholesale_price = $wholesale_price;
        $this->retail_price = $retail_price;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->is_available = $is_available;
        $this->is_visible = $is_visible;
        $this->special_offer = $special_offer;
        $this->minimum_wholesale = $minimum_wholesale;
        $this->sale = $sale;
        $this->product_value = $product_value;
        $this->product_unit = $product_unit;
        $this->slug = $slug;
        $this->currency_name = $currency_name;
        $this->brand = $brand;
        $this->image = $image;
        $this->id  = $id;
        $this->seo_description = $seo_description;
    }

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getWholesalePrice(): ?float
    {
        return $this->wholesale_price;
    }

    public function getRetailPrice(): float
    {
        return $this->retail_price;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updated_at;
    }

    public function isIsAvailable(): bool
    {
        return $this->is_available;
    }

    public function isIsVisible(): bool
    {
        return $this->is_visible;
    }

    public function isSpecialOffer(): bool
    {
        return $this->special_offer;
    }

    public function getMinimumWholesale(): ?int
    {
        return $this->minimum_wholesale;
    }

    public function getSale(): ?float
    {
        return $this->sale;
    }

    public function getProductUnit(): ?string
    {
        return $this->product_unit;
    }

    public function getProductValue(): ?float
    {
        return $this->product_value;
    }

    public function setProductValue(float $product_value): void
    {
        $this->product_value = $product_value;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCurrencyName(): ?string
    {
        return $this->currency_name;
    }

    /**
     * @return null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param null $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }



    /**
     * @return null
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param null $brand
     */
    public function setBrand($brand): void
    {
        $this->brand = $brand;
    }



    public function getImage()
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSpecifications()
    {
        return $this->specifications;
    }

    /**
     * @param mixed $specifications
     */
    public function setSpecifications($specifications): void
    {
        $this->specifications = $specifications;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param float $retail_price
     */
    public function setRetailPrice(float $retail_price): void
    {
        $this->retail_price = $retail_price;
    }

    /**
     * @return null
     */
    public function getSeoDescription()
    {
        return $this->seo_description;
    }

    /**
     * @param null $seo_description
     */
    public function setSeoDescription($seo_description): void
    {
        $this->seo_description = $seo_description;
    }

    









}