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
use App\Catalog\Repository\ProductImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductImageRepository::class)]
#[ORM\Index(name: 'idx_product_image_product_id', columns: ['product_id'])]
#[ApiResource(
    shortName: 'ProductImage',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['product_image:read']],
    denormalizationContext: ['groups' => ['product_image:write']],
    security: "is_granted('ROLE_ADMIN')",
)]
class ProductImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product_image:read', 'product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product_image:read', 'product_image:write', 'product:read'])]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product_image:read', 'product_image:write', 'product:read'])]
    private ?string $image_file_id = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(['product_image:read', 'product_image:write', 'product:read'])]
    private int $sort_order = 0;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['product_image:read', 'product_image:write'])]
    private ?Product $product = null;

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
