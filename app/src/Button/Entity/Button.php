<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Button\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Button\Repository\ButtonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ButtonRepository::class)]
#[ORM\Index(name: 'idx_button_bot_identifier', columns: ['bot_identifier'])]
#[ApiResource(
    shortName: 'Button',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['button:read']],
    denormalizationContext: ['groups' => ['button:write']],
    security: "is_granted('ROLE_ADMIN')",
)]
class Button
{
    public const TYPE_URL = 'url';
    public const TYPE_CALLBACK = 'callback';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['button:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['button:read'])]
    private ?string $bot_identifier = null;

    #[ORM\Column(length: 20)]
    #[Groups(['button:read', 'button:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private ?string $code = null;

    #[ORM\Column(length: 60)]
    #[Groups(['button:read', 'button:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 60)]
    private ?string $label = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    #[Groups(['button:read', 'button:write'])]
    #[SerializedName('sortOrder')]
    private int $sort_order = 0;

    #[ORM\Column(length: 20)]
    #[Groups(['button:read', 'button:write'])]
    #[SerializedName('buttonType')]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_URL, self::TYPE_CALLBACK], message: 'Button type must be either "url" or "callback".')]
    private ?string $button_type = null;

    #[ORM\Column(length: 60)]
    #[Groups(['button:read', 'button:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 60)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

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

    public function getButtonType(): ?string
    {
        return $this->button_type;
    }

    public function setButtonType(string $button_type): static
    {
        $this->button_type = $button_type;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
