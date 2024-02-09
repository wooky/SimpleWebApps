<?php

declare(strict_types=1);

namespace SimpleWebApps\Auth;

use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Traversable;

use function assert;
use function in_array;

readonly class AuthenticatedUser implements UserInterface, PasswordAuthenticatedUserInterface
{
  /**
   * @param Relationship[] $fromRelationships
   */
  public function __construct(public User $user, public array $fromRelationships)
  {
    // Do nothing.
  }

  /**
   * @param RelationshipCapability[] $capabilities
   */
  public function doesRelationshipExist(User $user, array $capabilities): bool
  {
    foreach ($this->fromRelationships as $relationship) {
      if ($relationship->getToUser() === $user) {
        return in_array($relationship->getCapability(), $capabilities, true);
      }
    }

    return false;
  }

  /**
   * @param RelationshipCapability[] $capabilities
   *
   * @return Traversable<int,User>
   */
  public function iterateControlledUsers(array $capabilities): Traversable
  {
    foreach ($this->fromRelationships as $relationship) {
      if (in_array($relationship->getCapability(), $capabilities, true)) {
        $toUser = $relationship->getToUser();
        assert(null !== $toUser);
        yield $toUser;
      }
    }
  }

  /**
   * A visual identifier that represents this user.
   *
   * @see UserInterface
   */
  public function getUserIdentifier(): string
  {
    $username = $this->user->getUsername();
    assert(null !== $username);

    return $username;
  }

  /**
   * @see UserInterface
   */
  public function getRoles(): array
  {
    $roles = $this->user->getRoles();
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  /**
   * @see UserInterface
   */
  public function eraseCredentials(): void
  {
    // If you store any temporary, sensitive data on the user, clear it here
    // $this->plainPassword = null;
  }

  /**
   * @see PasswordAuthenticatedUserInterface
   */
  public function getPassword(): ?string
  {
    return $this->user->getPassword();
  }
}
