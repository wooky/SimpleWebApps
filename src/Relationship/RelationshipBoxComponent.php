<?php declare(strict_types = 1);
namespace SimpleWebApps\Relationship;

use SimpleWebApps\Entity\Relationship;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

/**
 * @psalm-suppress MissingConstructor
 */
#[AsTwigComponent('relationship:relationship-box')]
class RelationshipBoxComponent
{
  public Relationship $relationship;
  public bool $isFromUser;

  #[ExposeInTemplate]
  public function getName(): string
  {
    $name = $this->isFromUser ? $this->relationship->getToUser()?->getUsername() : $this->relationship->getFromUser()?->getUsername();
    assert($name !== null);
    return $name;
  }
}

