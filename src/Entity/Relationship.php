<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Entity\Mixin\IdMixin;
use SimpleWebApps\Repository\RelationshipRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RelationshipRepository::class)]
#[ORM\UniqueConstraint(
  name: 'link_unique_idx',
  columns: ['from_user', 'to_user'],
)]
#[Assert\Expression('this.fromUser != this.toUser', message: 'relationships.to_self')]
#[Gedmo\Loggable]
class Relationship implements Identifiable
{
  use IdMixin;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(name: 'from_user', nullable: false)]
  private ?User $fromUser = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(name: 'to_user', nullable: false)]
  private ?User $toUser = null;

  #[ORM\Column(type: 'string', enumType: RelationshipCapability::class)]
  #[Gedmo\Versioned]
  private ?RelationshipCapability $capability = null;

  #[ORM\Column]
  #[Gedmo\Versioned]
  private bool $active = false;

  public function getFromUser(): ?User
  {
    return $this->fromUser;
  }

  public function setFromUser(?User $fromUser): self
  {
    $this->fromUser = $fromUser;

    return $this;
  }

  public function getToUser(): ?User
  {
    return $this->toUser;
  }

  public function setToUser(?User $toUser): self
  {
    $this->toUser = $toUser;

    return $this;
  }

  public function getCapability(): ?RelationshipCapability
  {
    return $this->capability;
  }

  public function setCapability(RelationshipCapability $capability): self
  {
    $this->capability = $capability;

    return $this;
  }

  public function isActive(): bool
  {
    return $this->active;
  }

  public function setActive(bool $active): self
  {
    $this->active = $active;

    return $this;
  }
}
