<?php

declare(strict_types=1);

namespace SimpleWebApps\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Entity\Mixin\IdMixin;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use function assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'auth.username_exists')]
#[Gedmo\Loggable]
class User implements PasswordAuthenticatedUserInterface, TwoFactorInterface, Identifiable
{
  use IdMixin;

  #[ORM\Column(length: 180, unique: true)]
  #[Gedmo\Versioned]
  private ?string $username = null;

  /**
   * @var string[]
   */
  #[ORM\Column]
  #[Gedmo\Versioned]
  private array $roles = [];

  /**
   * @var string The hashed password
   */
  #[ORM\Column]
  #[Gedmo\Versioned]
  private ?string $password = null;

  #[ORM\Column(length: 64, nullable: true)]
  #[Gedmo\Versioned]
  private ?string $googleAuthenticatorSecret = null;

  public function getUsername(): ?string
  {
    return $this->username;
  }

  public function setUsername(string $username): self
  {
    $this->username = $username;

    return $this;
  }

  /**
   * @return string[]
   */
  public function getRoles(): array
  {
    return $this->roles;
  }

  /**
   * @param string[] $roles
   */
  public function setRoles(array $roles): self
  {
    $this->roles = $roles;

    return $this;
  }

  /**
   * @see PasswordAuthenticatedUserInterface
   */
  public function getPassword(): ?string
  {
    return $this->password;
  }

  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  /**
   * @see TwoFactorInterface
   */
  public function getGoogleAuthenticatorSecret(): ?string
  {
    return $this->googleAuthenticatorSecret;
  }

  /**
   * @see TwoFactorInterface
   */
  public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): self
  {
    $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;

    return $this;
  }

  /**
   * @see TwoFactorInterface
   */
  public function isGoogleAuthenticatorEnabled(): bool
  {
    return null !== $this->googleAuthenticatorSecret;
  }

  /**
   * @see TwoFactorInterface
   */
  public function getGoogleAuthenticatorUsername(): string
  {
    assert(null !== $this->username);

    return $this->username;
  }
}
