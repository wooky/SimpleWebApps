<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum BookViewFilter: string implements TranslatableInterface
{
  case All = 'all';
  case Public = 'public';
  case PublicAbsent = 'public_absent';

  public function trans(TranslatorInterface $translator, ?string $locale = null): string
  {
    return $translator->trans('books.view_filter.'.$this->value, locale: $locale);
  }
}
