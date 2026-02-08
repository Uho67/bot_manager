<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Template\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Template\Repository\TemplateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[ORM\Index(name: 'idx_template_bot_identifier', columns: ['bot_identifier'])]
#[ORM\Index(name: 'idx_template_type', columns: ['type'])]
#[ApiResource(
    shortName: 'Template',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['template:read']],
    denormalizationContext: ['groups' => ['template:write']],
    security: "is_granted('ROLE_ADMIN')",
)]
class Template
{
    public const TYPE_POST = 'post';
    public const TYPE_START = 'start';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_PRODUCT = 'product';

    public const MAX_BUTTONS_PER_LINE = 8;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['template:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['template:read'])]
    private ?string $bot_identifier = null;

    #[ORM\Column(length: 100)]
    #[Groups(['template:read', 'template:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    #[Groups(['template:read', 'template:write'])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_POST, self::TYPE_START, self::TYPE_CATEGORY, self::TYPE_PRODUCT])]
    private ?string $type = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['template:read', 'template:write'])]
    #[Assert\NotBlank]
    #[SerializedName('layout')]
    private array $layout = [];

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLayout(): array
    {
        return $this->layout;
    }

    public function setLayout(array $layout): static
    {
        $this->layout = $layout;

        return $this;
    }

    #[Assert\Callback]
    public function validateLayout(mixed $context): void
    {
        if (!is_array($this->layout)) {
            return;
        }

        foreach ($this->layout as $lineIndex => $line) {
            if (!is_array($line)) {
                $context
                    ->buildViolation('Each line in layout must be an array.')
                    ->atPath('layout[' . $lineIndex . ']')
                    ->addViolation();
                continue;
            }

            $buttonCount = count($line);
            if ($buttonCount > self::MAX_BUTTONS_PER_LINE) {
                $context
                    ->buildViolation('A line cannot have more than {{ limit }} buttons.')
                    ->setParameter('{{ limit }}', (string) self::MAX_BUTTONS_PER_LINE)
                    ->atPath('layout[' . $lineIndex . ']')
                    ->addViolation();
            }

            foreach ($line as $buttonIndex => $buttonId) {
                if (!is_int($buttonId) || $buttonId <= 0) {
                    $context
                        ->buildViolation('Button ID must be a positive integer.')
                        ->atPath('layout[' . $lineIndex . '][' . $buttonIndex . ']')
                        ->addViolation();
                }
            }
        }
    }
}

