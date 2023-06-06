<?php

declare(strict_types=1);

namespace SimpleWebApps\Auth;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\UserRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * @psalm-suppress MissingConstructor
 */
#[AsLiveComponent('auth:mfa')]
class MfaComponent
{
  use DefaultActionTrait;

  #[LiveProp]
  public User $user;

  #[LiveProp]
  public ?string $qrContents = null;

  #[LiveAction]
  public function generate(
    UserRepository $userRepository,
    GoogleAuthenticatorInterface $googleAuthenticator,
  ): void {
    $secret = $googleAuthenticator->generateSecret();
    $this->user->setGoogleAuthenticatorSecret($secret);
    $userRepository->save($this->user, true);

    $this->qrContents = $googleAuthenticator->getQRContent($this->user);
  }

  #[LiveAction]
  public function remove(UserRepository $userRepository): void
  {
    $this->user->setGoogleAuthenticatorSecret(null);
    $userRepository->save($this->user, true);

    $this->qrContents = null;
  }
}
