<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use SimpleWebApps\Entity\BookOwnership;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

/**
 * @psalm-suppress MissingConstructor
 */
#[AsTwigComponent(self::NAME)]
class BookCard
{
  public const NAME = 'book:card';

  public BookOwnership $bookOwnership;

  #[ExposeInTemplate]
  public function getCardHtmlId(): string
  {
    return BookRenderingUtilities::cardHtmlId($this->bookOwnership->getBook()->getId());
  }

  #[ExposeInTemplate]
  public function getContentHtmlId(): string
  {
    return BookRenderingUtilities::contentHtmlId($this->bookOwnership->getBook()->getId());
  }
}
