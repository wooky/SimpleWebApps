<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship\Box;

use RuntimeException;
use SimpleWebApps\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @psalm-suppress MissingConstructor
 */
#[AsTwigComponent(self::NAME)]
final class RelationshipToBox extends AbstractRelationshipBox
{
  public const NAME = 'relationship:to-box';

  public function getUser(): User
  {
    return $this->relationship->getFromUser() ?? throw new RuntimeException('Relationship has no user');
  }
}
