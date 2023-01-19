<?php declare(strict_types = 1);
namespace SimpleWebApps\Auth;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum RelationshipCapability: string implements TranslatableInterface {
  case Read = 'read';
  case Write = 'write';

  /**
   * @return self[]
   */
  public function withPermissionsGranted(): array
  {
    return match ($this) {
      self::Read => [self::Read],
      self::Write => [self::Read, self::Write],
    };
  }

  /**
   * @return self[]
   */
  public function permissionsRequired(): array
  {
    return match ($this) {
      self::Read => [self::Read, self::Write],
      self::Write => [self::Write],
    };
  }

  public function trans(TranslatorInterface $translator, ?string $locale = null): string
  {
    return $translator->trans('relationships.capability.' . $this->value, locale: $locale);
  }
}
