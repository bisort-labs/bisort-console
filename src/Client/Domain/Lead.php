<?php

declare(strict_types=1);

namespace App\Client\Domain;

use App\Client\Domain\Enum\LeadSource;
use App\Client\Infrastructure\Persistence\LeadRepository;
use App\Shared\Domain\AbstractResource;
use App\User\Domain\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LeadRepository::class)]
class Lead extends AbstractResource
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Lead name cannot be empty.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Lead name cannot be less than {{ limit }} characters.',
        maxMessage: 'Lead name cannot be more than {{ limit }} characters.',
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Lead email cannot be empty.')]
    #[Assert\Email(message: 'Lead email must be a valid email address.')]
    #[Assert\Length(max: 255, maxMessage: 'Lead email cannot be more than {{ limit }} characters.')]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Company cannot be more than {{ limit }} characters.')]
    private ?string $company = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Phone cannot be more than {{ limit }} characters.')]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Street cannot be more than {{ limit }} characters.')]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Zip code cannot be more than {{ limit }} characters.')]
    private ?string $zipCode = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'City cannot be more than {{ limit }} characters.')]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'State cannot be more than {{ limit }} characters.')]
    private ?string $state = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Country cannot be more than {{ limit }} characters.')]
    private ?string $country = null;

    #[ORM\Column(enumType: LeadSource::class)]
    #[Assert\NotNull(message: 'Lead source must be selected.')]
    private ?LeadSource $source = null;

    #[ORM\ManyToOne(inversedBy: 'leads')]
    private ?User $owner = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getSource(): ?LeadSource
    {
        return $this->source;
    }

    public function setSource(LeadSource $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
