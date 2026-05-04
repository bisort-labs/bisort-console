<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\Client\Domain\Deal;
use App\Client\Domain\Lead;
use App\Shared\Domain\AbstractResource;
use App\User\Infrastructure\Persistence\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Override;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(
    fields: ['username'],
    message: 'Username already taken',
    errorPath: 'username',
)]
class User extends AbstractResource implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Username cannot be blank')]
    #[Assert\Length(
        min: 3,
        max: 180,
        minMessage: 'Username cannot be less than {{ limit }} characters',
        maxMessage: 'Username cannot be more than {{ limit }} characters',
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._-]+$/',
        message: 'Username may only contain letters, numbers, dots, underscores, and hyphens.',
    )]
    private ?string $username = null;

    /** @var list<string> */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(
        message: 'Password cannot be empty.',
    )]
    #[Assert\Length(
        min: 12,
        max: 255,
        minMessage: 'Password must contain at least {{ limit }} characters.',
        maxMessage: 'Password must contain at most {{ limit }} characters.',
    )]
    private ?string $password = null;

    /**
     * @var Collection<int, Lead>
     */
    #[ORM\OneToMany(targetEntity: Lead::class, mappedBy: 'owner')]
    private Collection $leads;

    /**
     * @var Collection<int, Deal>
     */
    #[ORM\OneToMany(targetEntity: Deal::class, mappedBy: 'owner')]
    private Collection $deals;

    public function __construct()
    {
        $this->leads = new ArrayCollection();
        $this->deals = new ArrayCollection();
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    #[Override]
    public function getUserIdentifier(): string
    {
        if ($this->username === null || $this->username === '') {
            throw new LogicException('User identifier is not set.');
        }

        return $this->username;
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    #[Override]
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;

        if (null !== $this->password) {
            $data["\0".self::class."\0password"] = hash('crc32c', $this->password);
        }

        return $data;
    }

    /**
     * @return Collection<int, Lead>
     */
    public function getLeads(): Collection
    {
        return $this->leads;
    }

    public function addLead(Lead $lead): static
    {
        if (!$this->leads->contains($lead)) {
            $this->leads->add($lead);
            $lead->setOwner($this);
        }

        return $this;
    }

    public function removeLead(Lead $lead): static
    {
        // set the owning side to null (unless already changed)
        if ($this->leads->removeElement($lead) && $lead->getOwner() === $this) {
            $lead->setOwner(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Deal>
     */
    public function getDeals(): Collection
    {
        return $this->deals;
    }

    public function addDeal(Deal $deal): static
    {
        if (!$this->deals->contains($deal)) {
            $this->deals->add($deal);
            $deal->setOwner($this);
        }

        return $this;
    }

    public function removeDeal(Deal $deal): static
    {
        if ($this->deals->removeElement($deal) && $deal->getOwner() === $this) {
            $deal->setOwner(null);
        }

        return $this;
    }
}
