<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Mailout\Repository\MailoutRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MailoutRepository::class)]
#[ORM\Table(name: 'mailout')]
#[ORM\Index(name: 'idx_mailout_bot_identifier', columns: ['bot_identifier'])]
#[ORM\Index(name: 'idx_mailout_product_id', columns: ['product_id'])]
#[ORM\Index(name: 'idx_mailout_chat_id', columns: ['chat_id'])]
#[ORM\Index(name: 'idx_mailout_status', columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    shortName: 'Mailout',
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['mailout:read']],
    security: "is_granted('ROLE_ADMIN')",
)]
class Mailout
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['mailout:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups(['mailout:read'])]
    private ?string $chat_id = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['mailout:read'])]
    private ?int $product_id = null;

    #[ORM\Column(length: 50, options: ['default' => self::STATUS_PENDING])]
    #[Groups(['mailout:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255)]
    #[Groups(['mailout:read'])]
    private ?string $bot_identifier = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['mailout:read'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['mailout:read'])]
    private ?\DateTimeImmutable $sent_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChatId(): ?string
    {
        return $this->chat_id;
    }

    public function setChatId(string $chat_id): static
    {
        $this->chat_id = $chat_id;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(int $product_id): static
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sent_at;
    }

    public function setSentAt(?\DateTimeImmutable $sent_at): static
    {
        $this->sent_at = $sent_at;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTimeImmutable();
        }
    }
}
