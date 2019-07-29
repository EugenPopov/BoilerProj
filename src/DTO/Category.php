<?php


namespace App\DTO;


use DateTimeInterface;

class Category
{
    private $parent_id;
    private $name;
    private $description;
    private $created_at;
    private $updated_at;
    private $image;
    private $is_visible;
    private $slug;
    private $usd_value;
    private $eur_value;
    private $seo_description;

    public function __construct(string $name, bool $is_visible, DateTimeInterface $created_at, DateTimeInterface $updated_at, string $slug, int $parent_id = null, string $description = null, string $image = null, float $usd_value = null, float $eur_value = null, $seo_description = null)
    {
        $this->parent_id = $parent_id;
        $this->name = $name;
        $this->description = $description;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->image = $image;
        $this->is_visible = $is_visible;
        $this->slug = $slug;
        $this->usd_value = $usd_value;
        $this->eur_value = $eur_value;
        $this->seo_description = $seo_description;
    }

    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updated_at;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function isIsVisible(): bool
    {
        return $this->is_visible;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getUsdValue(): ?float
    {
        return $this->usd_value;
    }

    public function getEurValue(): ?float
    {
        return $this->eur_value;
    }
}