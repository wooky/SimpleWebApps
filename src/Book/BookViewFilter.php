<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum BookViewFilter: string implements TranslatableInterface
{
  case All = 'all';
  case Own = 'own';
  case Wishlist = 'wishlist';
  case Public = 'public';
  case PublicAbsent = 'public_absent';

  public function toOwnershipState(): ?BookOwnershipState
  {
    return BookOwnershipState::tryFrom($this->value);
  }

  public function trans(TranslatorInterface $translator, ?string $locale = null): string
  {
    $state = $this->toOwnershipState();
    if ($state) {
      return $state->trans($translator, $locale);
    }

    return $translator->trans('books.view_filter.'.$this->value, locale: $locale);
  }
}
