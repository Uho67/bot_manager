<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Post\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post as ApiPost;
use ApiPlatform\Metadata\Put;
use App\Post\Repository\PostRepository;
use App\Template\Entity\Template;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Index(name: 'idx_post_bot_identifier', columns: ['bot_identifier'])]
#[ORM\Index(name: 'idx_post_template_type', columns: ['template_type'])]
#[ApiResource(
    shortName: 'Post',
    operations: [
        new GetCollection(),
        new Get(),
        new ApiPost(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['post:read']],
    denormalizationContext: ['groups' => ['post:write']],
    security: "is_granted('ROLE_ADMIN')",
)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['post:read', 'post:write'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['post:read', 'post:write'])]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['post:read', 'post:write'])]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['post:read', 'post:write'])]
    private ?string $image_file_id = null;

    #[ORM\Column(length: 20)]
    #[Groups(['post:read', 'post:write'])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [Template::TYPE_START, Template::TYPE_PRODUCT, Template::TYPE_POST])]
    private ?string $template_type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['post:read'])]
    private ?string $bot_identifier = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    #[Groups(['post:read', 'post:write'])]
    private bool $enabled = true;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
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

    public function getTemplateType(): ?string
    {
        return $this->template_type;
    }

    public function setTemplateType(string $template_type): static
    {
        $this->template_type = $template_type;

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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
