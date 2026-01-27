<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Config\Entity;

use App\Config\Repository\ConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
#[ORM\Table(name: 'config')]
#[ORM\Index(name: 'idx_bot_identifier', columns: ['bot_identifier'])]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['config:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['config:read', 'config:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $path = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['config:read', 'config:write'])]
    #[Assert\Length(max: 255)]
    private string $value = '0';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['config:read', 'config:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $bot_identifier = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['config:read', 'config:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
