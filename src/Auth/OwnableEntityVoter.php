<?php

declare(strict_types=1);

namespace SimpleWebApps\Auth;

use Psr\Log\LoggerInterface;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\RelationshipRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use function assert;

/**
 * @extends Voter<string,Ownable>
 */
class OwnableEntityVoter extends Voter
{
  public function __construct(
    private readonly RelationshipRepository $relationshipRepository,
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
    $user = $token->getUser();
    if (!$user instanceof User) {
      $this->logger->debug('User is not logged in.');

      return false;
    }

    $this->logger->debug('Check if entity owner matches user.', [
      'entity_owner' => $subject->getOwner()?->getId(),
      'user_id' => $user->getId(),
    ]);
    $owner = $subject->getOwner();
    assert(null !== $owner);
    if ($owner === $user) {
      return true;
    }

    $this->logger->debug('Check if relationship exists');
    $capability = RelationshipCapability::from($attribute);
    $relationship =
      $this->relationshipRepository->findActiveRelationship($user, $owner, $capability->permissionsRequired());
    if ($relationship) {
      return true;
    }

    return false;
  }
}