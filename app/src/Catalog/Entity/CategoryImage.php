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
use App\Catalog\Repository\CategoryImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CategoryImageRepository::class)]
#[ORM\Index(name: 'idx_category_image_category_id', columns: ['category_id'])]
#[ApiResource(
    shortName: 'CategoryImage',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['category_image:read']],
    denormalizationContext: ['groups' => ['category_image:write']],
    security: "is_granted('ROLE_ADMIN')",
)]
class CategoryImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category_image:read', 'category:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['category_image:read', 'category_image:write', 'category:read'])]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['category_image:read', 'category_image:write', 'category:read'])]
    private ?string $image_file_id = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['category_image:read', 'category_image:write', 'category:read'])]
    private int $sort_order = 0;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['category_image:read', 'category_image:write'])]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getImageFileId(): ?string
    {
        return $this->image_file_id;
    }

    public function setImageFileId(?string $image_file_id): static
    {
        $this->image_file_id = $image_file_id;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sort_order;
    }

    public function setSortOrder(int $sort_order): static
    {
        $this->sort_order = $sort_order;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
