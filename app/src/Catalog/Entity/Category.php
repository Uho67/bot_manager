<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Catalog\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Catalog\Repository\CategoryRepository;
use App\Catalog\Validator\ValidCategoryChildren;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Index(name: 'idx_category_bot_identifier', columns: ['bot_identifier'])]
#[ApiResource(
    shortName: 'Category',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    security: "is_granted('ROLE_ADMIN')"
)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read', 'product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['category:read', 'category:write', 'product:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['category:read'])]
    private ?string $bot_identifier = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'categories')]
    #[Groups(['category:read'])]
    private Collection $products;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class)]
    #[ORM\JoinTable(name: 'category_children')]
    #[Groups(['category:read', 'category:write'])]
    #[Assert\Count(max: 10, maxMessage: 'A category cannot have more than {{ limit }} children.')]
    #[ValidCategoryChildren]
    private Collection $childCategories;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->childCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getBotIdentifier(): ?string
    {
        return $this->bot_identifier;
    }

    public function setBotIdentifier(string $bot_identifier): static
    {
        $this->bot_identifier = $bot_identifier;
        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            $product->removeCategory($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getChildCategories(): Collection
    {
        return $this->childCategories;
    }

    public function addChildCategory(Category $childCategory): static
    {
        if (!$this->childCategories->contains($childCategory)) {
            $this->childCategories->add($childCategory);
        }

        return $this;
    }

    public function removeChildCategory(Category $childCategory): static
    {
        $this->childCategories->removeElement($childCategory);

        return $this;
    }
}

