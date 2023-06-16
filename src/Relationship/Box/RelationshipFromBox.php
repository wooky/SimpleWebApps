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
final class RelationshipFromBox extends AbstractRelationshipBox
{
  public const NAME = 'relationship:from-box';

  public function getUser(): User
  {
    return $this->relationship->getToUser() ?? throw new RuntimeException('Relationship has no user');
  }
}
