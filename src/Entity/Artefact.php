<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use SimpleWebApps\Repository\ArtefactRepository;

use function assert;

#[ORM\Entity(repositoryClass: ArtefactRepository::class)]
#[Gedmo\Loggable]
#[Gedmo\Uploadable(filenameGenerator: 'SHA1')]
class Artefact
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 64)]
  #[Gedmo\UploadableFileName]
  #[Gedmo\Versioned]
  private ?string $filePath = null;

  public function getId(): int
  {
    assert(null !== $this->id);

    return $this->id;
  }

  public function getFilePath(): string
  {
    assert(null !== $this->filePath);

    return $this->filePath;
  }

  public function setFilePath(string $filePath): self
  {
    $this->filePath = $filePath;

    return $this;
  }
}
