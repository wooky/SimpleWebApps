<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Repository\WeightRecordRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

use function assert;

#[ORM\Entity(repositoryClass: WeightRecordRepository::class)]
#[ORM\UniqueConstraint(
  name: 'weight_record_date_unique_idx',
  columns: ['owner_id', 'date'],
)]
class WeightRecord implements Identifiable, Ownable
{
  #[ORM\Id]
  #[ORM\Column(type: 'ulid', unique: true)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
  private ?Ulid $id = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $owner = null;

  #[ORM\Column(type: Types::DATE_IMMUTABLE)]
  private ?DateTimeImmutable $date = null;

  #[ORM\Column(type: Types::SMALLINT)]
  #[Assert\Positive]
  private ?int $weight = null;

  public function getId(): Ulid
  {
    assert(null !== $this->id);

    return $this->id;
  }

  public function getIdOrNull(): ?Ulid
  {
    return $this->id;
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
