<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Mailout\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Mailout\Repository\PostMailoutRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PostMailoutRepository::class)]
#[ORM\Table(name: 'post_mailout')]
#[ORM\Index(name: 'idx_post_mailout_bot_identifier', columns: ['bot_identifier'])]
#[ORM\Index(name: 'idx_post_mailout_post_id', columns: ['post_id'])]
#[ORM\Index(name: 'idx_post_mailout_chat_id', columns: ['chat_id'])]
#[ORM\Index(name: 'idx_post_mailout_status', columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    shortName: 'PostMailout',
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['post_mailout:read']],
    security: "is_granted('ROLE_ADMIN')",
)]
class PostMailout
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    public const REMOVE_MODE_REMOVE = 'remove';
    public const REMOVE_MODE_NOT_REMOVE = 'not_remove';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post_mailout:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups(['post_mailout:read'])]
    private ?string $chat_id = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['post_mailout:read'])]
    private ?int $post_id = null;

    #[ORM\Column(length: 50, options: ['default' => self::STATUS_PENDING])]
    #[Groups(['post_mailout:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255)]
    #[Groups(['post_mailout:read'])]
    private ?string $bot_identifier = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['post_mailout:read'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 20, options: ['default' => self::REMOVE_MODE_REMOVE])]
    #[Groups(['post_mailout:read'])]
    private string $remove_mode = self::REMOVE_MODE_REMOVE;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['post_mailout:read'])]
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

    public function getPostId(): ?int
    {
        return $this->post_id;
    }

    public function setPostId(int $post_id): static
    {
        $this->post_id = $post_id;

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

    public function getRemoveMode(): string
    {
        return $this->remove_mode;
    }

    public function setRemoveMode(string $remove_mode): static
    {
        $this->remove_mode = $remove_mode;

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
