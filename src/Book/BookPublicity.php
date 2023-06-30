<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum BookPublicity: string implements TranslatableInterface
{
  case Private = 'private';
  case PublicReadonly = 'public_readonly';
  case PublicCommunity = 'public_community';

  public function isPublic(): bool
  {
    return self::PublicReadonly === $this || self::PublicCommunity === $this;
  }

  /**
   * @return self[]
   */
  public function getAllowedChangedValues(): array
  {
    return match ($this) {
      self::Private => [self::Private, self::PublicReadonly, self::PublicCommunity],
      self::PublicReadonly => [self::PublicReadonly, self::PublicCommunity],
      self::PublicCommunity => [self::PublicCommunity],
    };
  }

  /**
   * @return string[]
   */
  public static function getPublicSqlValues(): array
  {
    return [self::PublicReadonly->value, self::PublicCommunity->value];
  }

  public function trans(TranslatorInterface $translator, ?string $locale = null): string
  {
    return $translator->trans('books.publicity.'.$this->value, locale: $locale);
  }
}
