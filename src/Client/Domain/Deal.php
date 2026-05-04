<?php

declare(strict_types=1);

namespace App\Client\Domain;

use App\Client\Domain\Enum\DealStage;
use App\Client\Infrastructure\Persistence\DealRepository;
use App\Shared\Domain\AbstractResource;
use App\User\Domain\User;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DealRepository::class)]
class Deal extends AbstractResource
{
    #[ORM\ManyToOne(inversedBy: 'deals')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Lead must be selected.')]
    private ?Lead $lead = null;

    #[ORM\ManyToOne(inversedBy: 'deals')]
    private ?ClientProject $project = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Deal title cannot be empty.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Deal title cannot be less than {{ limit }} characters.',
        maxMessage: 'Deal title cannot be more than {{ limit }} characters.',
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $closeDate = null;

    #[ORM\Column(type: Types::STRING, length: 3)]
    #[Assert\NotBlank(message: 'Deal currency cannot be empty.')]
    #[Assert\Currency(message: 'Deal currency must be a valid ISO 4217 currency code.')]
    private ?string $currency = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull(message: 'Expected value must be set.')]
    #[Assert\PositiveOrZero(message: 'Expected value cannot be negative.')]
    private ?int $expectedValueCents = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'Lost reason cannot be more than {{ limit }} characters.')]
    private ?string $lostReason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'deals')]
    private ?User $owner = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\Range(notInRangeMessage: 'Probability must be between {{ min }} and {{ max }}.', min: 0, max: 100)]
    private ?int $probability = null;

    #[ORM\Column(enumType: DealStage::class)]
    #[Assert\NotNull(message: 'Deal stage must be selected.')]
    private ?DealStage $stage = null;

    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    public function setLead(?Lead $lead): self
    {
        $this->lead = $lead;

        return $this;
    }

    public function getProject(): ?ClientProject
    {
        return $this->project;
    }

    public function setProject(?ClientProject $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCloseDate(): ?DateTimeImmutable
    {
        return $this->closeDate;
    }

    public function setCloseDate(?DateTimeImmutable $closeDate): self
    {
        $this->closeDate = $closeDate;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getExpectedValueCents(): ?int
    {
        return $this->expectedValueCents;
    }

    public function setExpectedValueCents(int $expectedValueCents): self
    {
        $this->expectedValueCents = $expectedValueCents;

        return $this;
    }

    public function getLostReason(): ?string
    {
        return $this->lostReason;
    }

    public function setLostReason(?string $lostReason): self
    {
        $this->lostReason = $lostReason;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

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

    public function getProbability(): ?int
    {
        return $this->probability;
    }

    public function setProbability(?int $probability): self
    {
        $this->probability = $probability;

        return $this;
    }

    public function getStage(): ?DealStage
    {
        return $this->stage;
    }

    public function setStage(DealStage $stage): self
    {
        $this->stage = $stage;

        return $this;
    }
}
