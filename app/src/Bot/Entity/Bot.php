<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Bot\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Bot\Repository\BotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BotRepository::class)]
#[ApiResource(
    shortName: 'Bot',
    operations: [
        new GetCollection(),
        new Get(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Post(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Patch(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Put(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Delete(security: "is_granted('ROLE_SUPER_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['bot:read']],
    denormalizationContext: ['groups' => ['bot:write']],
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
)]
class Bot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['bot:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['bot:read', 'bot:write'])]
    private ?string $bot_identifier = null;

    #[ORM\Column(length: 50)]
    #[Groups(['bot:read', 'bot:write'])]
    private ?string $bot_code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['bot:read', 'bot:write'])]
    private ?string $api_key = null;

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

    public function getBotCode(): ?string
    {
        return $this->bot_code;
    }

    public function setBotCode(string $bot_code): static
    {
        $this->bot_code = $bot_code;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->api_key;
    }

    public function setApiKey(string $api_key): static
    {
        $this->api_key = $api_key;

        return $this;
    }
}
