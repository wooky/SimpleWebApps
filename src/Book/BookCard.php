<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use SimpleWebApps\Entity\BookOwnership;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @psalm-suppress MissingConstructor
 */
#[AsTwigComponent(self::NAME)]
class BookCard
{
  public const NAME = 'book:card';

  public BookOwnership $bookOwnership;
}
