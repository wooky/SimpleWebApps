<?php declare(strict_types = 1);
namespace SimpleWebApps\Auth;

enum RelationshipCapability: string {
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
}
