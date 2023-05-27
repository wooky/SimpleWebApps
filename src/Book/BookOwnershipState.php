<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum BookOwnershipState: string implements TranslatableInterface
{
  case Own = 'own';
  case Wishlist = 'wishlist';

  public function trans(TranslatorInterface $translator, ?string $locale = null): string
  {
    return $translator->trans('book.ownership.'.$this->value, locale: $locale);
  }
}
