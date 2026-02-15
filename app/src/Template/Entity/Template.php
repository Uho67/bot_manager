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
use App\Catalog\Constants\ButtonConstants;
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
    public const string TYPE_POST = 'post';
    public const string TYPE_START = 'start';
    public const string TYPE_PRODUCT = 'product';

    public const int MAX_BUTTONS_PER_LINE = 8;

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
    #[Assert\Choice(choices: [self::TYPE_POST, self::TYPE_START, self::TYPE_PRODUCT])]
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
                $isValid = false;

                // Accept positive integers (backward compatibility)
                if (is_int($buttonId) && $buttonId > 0) {
                    $isValid = true;
                }
                // Accept strings with valid prefixes (button_, category_, product_) followed by positive integer
                elseif (is_string($buttonId) && $buttonId !== '') {
                    $validPrefixes = [
                        ButtonConstants::PREFIX_BUTTON,
                        ButtonConstants::PREFIX_CATEGORY,
                        ButtonConstants::PREFIX_PRODUCT,
                    ];

                    foreach ($validPrefixes as $prefix) {
                        if (str_starts_with($buttonId, $prefix)) {
                            $numericPart = substr($buttonId, strlen($prefix));
                            if (is_numeric($numericPart) && (int) $numericPart > 0) {
                                $isValid = true;
                                break;
                            }
                        }
                    }
                }

                if (!$isValid) {
                    $context
                        ->buildViolation('Button ID must be a positive integer or a string with prefix (button_, category_, product_) followed by a positive integer.')
                        ->atPath('layout[' . $lineIndex . '][' . $buttonIndex . ']')
                        ->addViolation();
                }
            }
        }
    }
}

