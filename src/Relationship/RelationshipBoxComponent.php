<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship;

use function assert;

use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
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
  public function getUser(): User
  {
    $user = $this->isFromUser ? $this->relationship->getToUser() : $this->relationship->getFromUser();
    assert(null !== $user);

    return $user;
  }
}
