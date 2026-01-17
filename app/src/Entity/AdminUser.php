<?php

namespace App\Entity;

use App\Repository\AdminUserRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;


#[ORM\Entity(repositoryClass: AdminUserRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(), // GET /admin_users
        new Get(),           // GET /admin_users/{id}
        new Post(),          // POST /admin_users
        new Put(),           // PUT /admin_users/{id}
        new Delete()         // DELETE /admin_users/{id}
    ]
)]
class AdminUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $admin_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $admin_password = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $bot_code = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_super = null;

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
