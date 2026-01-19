<?php

namespace App\AdminUser\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\AdminUser\Repository\AdminUserRepository;
use App\AdminUser\State\AdminUserPasswordProcessor;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;


#[ORM\Entity(repositoryClass: AdminUserRepository::class)]
#[ApiResource(
    shortName: 'AdminUser',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(processor: AdminUserPasswordProcessor::class),
        new Put(processor: AdminUserPasswordProcessor::class),
        new Delete()
    ],
    normalizationContext: ['groups' => ['admin_user:read']],
    denormalizationContext: ['groups' => ['admin_user:write']]
)]
class AdminUser implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['admin_user:read'])]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    #[ORM\Column(length: 20)]
    #[Groups(['admin_user:read', 'admin_user:write'])]
    private ?string $admin_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['admin_user:write'])]
    private ?string $admin_password = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['admin_user:read', 'admin_user:write'])]
    private ?string $bot_code = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['admin_user:read'])]
    private ?bool $is_super = null;

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

    public function isSuper(): ?bool
    {
        return $this->is_super;
    }

    public function setIsSuper(?bool $is_super): static
    {
        $this->is_super = $is_super;

        return $this;
    }
}
