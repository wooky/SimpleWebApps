<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Entity\Mixin\IdMixin;
use SimpleWebApps\Repository\WeightRecordRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WeightRecordRepository::class)]
#[ORM\UniqueConstraint(
  name: 'weight_record_date_unique_idx',
  columns: ['owner_id', 'date'],
)]
#[UniqueEntity(['owner', 'date'], errorPath: 'date', message: 'weight_record.date_exists')]
#[Gedmo\Loggable]
class WeightRecord implements Identifiable, Ownable
{
  use IdMixin;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $owner = null;

  #[ORM\Column(type: Types::DATE_IMMUTABLE)]
  #[Gedmo\Versioned]
  private ?DateTimeImmutable $date = null;

  #[ORM\Column(type: Types::SMALLINT)]
  #[Gedmo\Versioned]
  #[Assert\Positive]
  private ?int $weight = null;

  public function getOwner(): ?User
  {
    return $this->owner;
  }

  public function setOwner(?User $owner): self
  {
    $this->owner = $owner;

    return $this;
  }

  public function getDate(): ?DateTimeImmutable
  {
    return $this->date;
  }

  public function setDate(DateTimeImmutable $date): self
  {
    $this->date = $date;

    return $this;
  }

  public function getWeight(): ?int
  {
    return $this->weight;
  }

  public function setWeight(int $weight): self
  {
    $this->weight = $weight;

    return $this;
  }
}
