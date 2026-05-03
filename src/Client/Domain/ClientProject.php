<?php

declare(strict_types=1);

namespace App\Client\Domain;

use App\Client\Infrastructure\Persistence\ClientProjectRepository;
use App\Shared\Domain\AbstractResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientProjectRepository::class)]
#[ORM\Table(name: 'client_projects')]
class ClientProject extends AbstractResource
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Project title cannot be empty.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Project title cannot be less than {{ limit }} characters.',
        maxMessage: 'Project title cannot be more than {{ limit }} characters.',
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
