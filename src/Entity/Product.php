<?php

namespace App\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=100, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $wholesale_price;

    /**
     * @ORM\Column(type="float")
     */
    private $retail_price;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Image", mappedBy="product", cascade={"remove"})
     */
    private $images;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_available;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_visible;

    /**
     * @ORM\Column(type="boolean")
     */
    private $special_offer;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Product", inversedBy="products")
     */
    private $recommend_product;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Product", mappedBy="recommend_product", cascade={"remove"})
     */
    private $products;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minimum_wholesale;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $sale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $product_unit;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Specification", mappedBy="product", cascade={"remove"})
     */
    private $specifications;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $currency_name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Brand", inversedBy="products")
     */
    private $brand;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_on_main;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $seo_description;

    /**
     * Product constructor.
     * @param $name
     * @param $description
     * @param null $currency_name
     * @param $wholesale_price
     * @param $retail_price
     * @param $is_available
     * @param $is_visible
     * @param $special_offer
     * @param $product_unit
     * @param $brand
     * @param null $sale
     * @param null $is_on_main
     * @throws \Exception
     */
    public function __construct($name = null, $description = null, $currency_name = null, $wholesale_price = null, $retail_price = null, $is_available = null, $is_visible = null, $special_offer = null, $product_unit = null, $brand = null, $sale = null, $is_on_main = null, $minimum_wholesale = null, $seo_description = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->wholesale_price = $wholesale_price;
        $this->retail_price = $retail_price;
        $this->is_available = $is_available;
        $this->is_visible = $is_visible;
        $this->special_offer = $special_offer;
        $this->product_unit = $product_unit;
        $this->brand = $brand;
        $this->currency_name = $currency_name;
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->is_visible = true;
        $this->images = new ArrayCollection();
        $this->recommend_product = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->specifications = new ArrayCollection();
        $this->sale = $sale;
        $this->is_on_main = $is_on_main;
        $this->minimum_wholesale = $minimum_wholesale;
        $this->seo_description = $seo_description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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



    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getWholesalePrice(): ?float
    {
        return $this->wholesale_price;
    }

    public function setWholesalePrice(?float $wholesale_price): self
    {
        $this->wholesale_price = $wholesale_price;

        return $this;
    }

    public function getRetailPrice(): ?float
    {
        return $this->retail_price;
    }

    public function setRetailPrice(float $retail_price): self
    {
        $this->retail_price = $retail_price;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProduct($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getIsAvailable(): ?bool
    {
        return $this->is_available;
    }

    public function setIsAvailable(bool $is_available): self
    {
        $this->is_available = $is_available;

        return $this;
    }

    public function getIsVisible(): ?bool
    {
        return $this->is_visible;
    }

    public function setIsVisible(bool $is_visible): self
    {
        $this->is_visible = $is_visible;

        return $this;
    }

    public function getSpecialOffer(): ?bool
    {
        return $this->special_offer;
    }

    public function setSpecialOffer(bool $special_offer): self
    {
        $this->special_offer = $special_offer;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getRecommendProduct(): Collection
    {
        return $this->recommend_product;
    }

    public function addRecommendProduct(self $recommendProduct): self
    {
        if (!$this->recommend_product->contains($recommendProduct)) {
            $this->recommend_product[] = $recommendProduct;
        }

        return $this;
    }

    public function removeRecommendProduct(self $recommendProduct): self
    {
        if ($this->recommend_product->contains($recommendProduct)) {
            $this->recommend_product->removeElement($recommendProduct);
        }

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(self $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->addRecommendProduct($this);
        }

        return $this;
    }

    public function removeProduct(self $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            $product->removeRecommendProduct($this);
        }

        return $this;
    }

    public function getMinimumWholesale(): ?int
    {
        return $this->minimum_wholesale;
    }

    public function setMinimumWholesale(?int $minimum_wholesale): self
    {
        $this->minimum_wholesale = $minimum_wholesale;

        return $this;
    }

    public function getSale(): ?float
    {
        return $this->sale;
    }

    public function setSale(?float $sale): self
    {
        $this->sale = $sale;

        return $this;
    }

    public function getProductUnit(): ?string
    {
        return $this->product_unit;
    }

    public function setProductUnit(?string $product_unit): self
    {
        $this->product_unit = $product_unit;

        return $this;
    }

    /**
     * @return Collection|Specification[]
     */
    public function getSpecifications(): Collection
    {
        return $this->specifications;
    }

    public function addSpecification(Specification $specification): self
    {
        if (!$this->specifications->contains($specification)) {
            $this->specifications[] = $specification;
            $specification->setProduct($this);
        }

        return $this;
    }

    public function removeSpecification(Specification $specification): self
    {
        if ($this->specifications->contains($specification)) {
            $this->specifications->removeElement($specification);
            if ($specification->getProduct() === $this) {
                $specification->setProduct(null);
            }
        }

        return $this;
    }

    public function getCurrencyName(): ?string
    {
        return $this->currency_name;
    }

    public function setCurrencyName(string $currency_name): self
    {
        $this->currency_name = $currency_name;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getIsOnMain(): ?bool
    {
        return $this->is_on_main;
    }

    public function setIsOnMain(bool $is_on_main): self
    {
        $this->is_on_main = $is_on_main;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function setSeoDescription(?string $seo_description): self
    {
        $this->seo_description = $seo_description;

        return $this;
    }
}
