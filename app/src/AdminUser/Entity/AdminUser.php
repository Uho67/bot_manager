<?php
/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\AdminUser\Entity;

use App\AdminUser\Repository\AdminUserRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;


#[ORM\Entity(repositoryClass: AdminUserRepository::class)]
#[ApiResource(
    shortName: 'AdminUser',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['admin_user:read']],
    denormalizationContext: ['groups' => ['admin_user:write']],
    security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')"
)]
class AdminUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['admin_user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Groups(['admin_user:read', 'admin_user:write'])]
    #[SerializedName('admin_name')]
    private ?string $admin_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['admin_user:write'])]
    #[SerializedName('admin_password')]
    private ?string $admin_password = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['admin_user:read', 'admin_user:write'])]
    #[SerializedName('bot_code')]
    private ?string $bot_code = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['admin_user:read', 'admin_user:write'])]
    #[SerializedName('bot_identifier')]
    private ?string $bot_identifier = null;

    /**
     * Roles for admin user. Example values:
     * ["ROLE_ADMIN"] or ["ROLE_SUPER_ADMIN"]
     * Only users with ROLE_SUPER_ADMIN can access AdminUser API.
     */
    #[ORM\Column(type: 'json')]
    #[Groups(['admin_user:read', 'admin_user:write'])]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdminName(): ?string
    {
        return $this->admin_name;
    }

    public function setAdminName(string $admin_name): static
    {
        $this->admin_name = $admin_name;

        return $this;
    }

    public function getAdminPassword(): ?string
    {
        return $this->admin_password;
    }

    public function getPassword(): ?string
    {
        return $this->admin_password;
    }

    public function setAdminPassword(?string $admin_password): static
    {
        $this->admin_password = $admin_password;

        return $this;
    }

    public function getBotCode(): ?string
    {
        return $this->bot_code;
    }

    public function setBotCode(?string $bot_code): static
    {
        $this->bot_code = $bot_code;

        return $this;
    }

    public function getBotIdentifier(): ?string
    {
        return $this->bot_identifier;
    }

    public function setBotIdentifier(?string $bot_identifier): static
    {
        $this->bot_identifier = $bot_identifier;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->admin_name ?? '';
    }

    public function eraseCredentials(): void
    {
        // Clear temporary sensitive data if needed
    }
}
