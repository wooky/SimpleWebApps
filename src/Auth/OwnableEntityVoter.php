<?php

declare(strict_types=1);

namespace SimpleWebApps\Auth;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function assert;

/**
 * @extends Voter<string,Ownable>
 */
class OwnableEntityVoter extends Voter
{
  public function __construct(
    private readonly LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  protected function supports(string $attribute, mixed $subject): bool
  {
    $this->logger->debug('Asking for support of UserVoter.', [
      'attribute' => $attribute,
      'is_ownable' => $subject instanceof Ownable,
    ]);
    $capability = RelationshipCapability::tryFrom($attribute);

    return $capability && $subject instanceof Ownable && null !== $subject->getOwner();
  }

  /**
   * @param Ownable $subject
   */
  protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
  {
    $authenticatedUser = $token->getUser();
    if (!$authenticatedUser instanceof AuthenticatedUser) {
      $this->logger->debug('User is not logged in.', ['authenticatedUser' => $authenticatedUser]);

      return false;
    }

    $this->logger->debug('Check if entity owner matches user.', [
      'entity_owner' => $subject->getOwner()?->getId(),
      'user_id' => $authenticatedUser->user->getId(),
    ]);
    $owner = $subject->getOwner();
    assert(null !== $owner);
    if ($owner === $authenticatedUser->user) {
      return true;
    }

    $this->logger->debug('Check if relationship exists');
    $capabilities = RelationshipCapability::from($attribute)->permissionsRequired();

    return $authenticatedUser->doesRelationshipExist($owner, $capabilities);
  }
}
