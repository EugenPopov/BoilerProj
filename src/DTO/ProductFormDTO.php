<?php


namespace App\DTO;


class ProductFormDTO
{
    private $category;
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
    private $images;
    private $currency_name;
    private $brand;
    private $amount;
    private $specification;


    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category): void
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getWholesalePrice()
    {
        return $this->wholesale_price;
    }

    /**
     * @param mixed $wholesale_price
     */
    public function setWholesalePrice($wholesale_price): void
    {
        $this->wholesale_price = $wholesale_price;
    }

    /**
     * @return mixed
     */
    public function getRetailPrice()
    {
        return $this->retail_price;
    }

    /**
     * @param mixed $retail_price
     */
    public function setRetailPrice($retail_price): void
    {
        $this->retail_price = $retail_price;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param mixed $updated_at
     */
    public function setUpdatedAt($updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @return mixed
     */
    public function getIsAvailable()
    {
        return $this->is_available;
    }

    /**
     * @param mixed $is_available
     */
    public function setIsAvailable($is_available): void
    {
        $this->is_available = $is_available;
    }

    /**
     * @return mixed
     */
    public function getIsVisible()
    {
        return $this->is_visible;
    }

    /**
     * @param mixed $is_visible
     */
    public function setIsVisible($is_visible): void
    {
        $this->is_visible = $is_visible;
    }

    /**
     * @return mixed
     */
    public function getSpecialOffer()
    {
        return $this->special_offer;
    }

    /**
     * @param mixed $special_offer
     */
    public function setSpecialOffer($special_offer): void
    {
        $this->special_offer = $special_offer;
    }

    /**
     * @return mixed
     */
    public function getMinimumWholesale()
    {
        return $this->minimum_wholesale;
    }

    /**
     * @param mixed $minimum_wholesale
     */
    public function setMinimumWholesale($minimum_wholesale): void
    {
        $this->minimum_wholesale = $minimum_wholesale;
    }

    /**
     * @return mixed
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * @param mixed $sale
     */
    public function setSale($sale): void
    {
        $this->sale = $sale;
    }

    /**
     * @return mixed
     */
    public function getProductValue()
    {
        return $this->product_value;
    }

    /**
     * @param mixed $product_value
     */
    public function setProductValue($product_value): void
    {
        $this->product_value = $product_value;
    }

    /**
     * @return mixed
     */
    public function getProductUnit()
    {
        return $this->product_unit;
    }

    /**
     * @param mixed $product_unit
     */
    public function setProductUnit($product_unit): void
    {
        $this->product_unit = $product_unit;
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
     */
    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param mixed $images
     */
    public function setImages($images): void
    {
        $this->images = $images;
    }

    /**
     * @return mixed
     */
    public function getCurrencyName()
    {
        return $this->currency_name;
    }

    /**
     * @param mixed $currency_name
     */
    public function setCurrencyName($currency_name): void
    {
        $this->currency_name = $currency_name;
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param mixed $brand
     */
    public function setBrand($brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @param mixed $specification
     */
    public function setSpecification($specification): void
    {
        $this->specification = $specification;
    }

}