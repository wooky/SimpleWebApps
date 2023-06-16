<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship\Box;

use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

abstract class AbstractRelationshipBox
{
  public Relationship $relationship;

  #[ExposeInTemplate]
  abstract public function getUser(): User;

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
