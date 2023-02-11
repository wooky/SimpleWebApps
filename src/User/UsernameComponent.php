<?php

declare(strict_types=1);

namespace SimpleWebApps\User;

use SimpleWebApps\Entity\User;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

/**
 * @psalm-suppress MissingConstructor
 */
#[AsTwigComponent(UsernameComponent::NAME)]
class UsernameComponent
{
  public const NAME = 'user:username';

  public User $user;

  #[ExposeInTemplate]
  public function getHtmlClass(): string
  {
    return self::htmlClass($this->user);
  }

  public static function htmlClass(User $user): string
  {
    return "simplewebapps-user-{$user->getId()}";
  }
}
