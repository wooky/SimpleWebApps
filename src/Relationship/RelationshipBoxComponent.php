<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship;

use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

use function assert;

/**
 * @psalm-suppress MissingConstructor
 */
#[AsTwigComponent(self::NAME)]
class RelationshipBoxComponent
{
  public const NAME = 'relationship:relationship-box';

  public Relationship $relationship;
  public bool $isFromUser;

  #[ExposeInTemplate]
  public function getUser(): User
  {
    $user = $this->isFromUser ? $this->relationship->getToUser() : $this->relationship->getFromUser();
    assert(null !== $user);

    return $user;
  }

  #[ExposeInTemplate]
  public function getHtmlId(): string
  {
    return self::htmlId((string) $this->relationship->getId());
  }

  public static function htmlId(string $relationshipId): string
  {
    return "simplewebapps-relationship-$relationshipId";
  }
}
