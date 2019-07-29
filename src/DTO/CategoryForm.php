<?php


namespace App\DTO;


class CategoryForm
{
    private $name;
    private $description;
    private $image;
    private $usd;
    private $eur;
    private $is_visible;
    private $seo_description;

    /**
     * CategoryForm constructor.
     * @param $name
     * @param $description
     * @param $usd
     * @param $eur
     * @param $is_visible
     * @param $image
     */
    public function __construct($name = null, $description = null, $usd = null, $eur = null, $is_visible = null, $image = null, $seo_description = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->usd = $usd;
        $this->eur = $eur;
        $this->is_visible = $is_visible;
        $this->seo_description = $seo_description;
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
     * @return CategoryForm
     */
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
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
     * @return CategoryForm
     */
    public function setDescription($description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     * @return CategoryForm
     */
    public function setImage($image): self
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsd()
    {
        return $this->usd;
    }

    /**
     * @param mixed $usd
     * @return CategoryForm
     */
    public function setUsd($usd): self
    {
        $this->usd = $usd;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEur()
    {
        return $this->eur;
    }

    /**
     * @param mixed $eur
     * @return CategoryForm
     */
    public function setEur($eur): self
    {
        $this->eur = $eur;
        return $this;
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