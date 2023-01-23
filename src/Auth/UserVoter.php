<?php

declare(strict_types=1);

namespace SimpleWebApps\Auth;

use function assert;

use Psr\Log\LoggerInterface;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\RelationshipRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
  public function __construct(
    private RelationshipRepository $relationshipRepository,
    private LoggerInterface $logger,
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

    return $capability && $subject instanceof Ownable;
  }

  protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
  {
    $user = $token->getUser();
    if (!$user instanceof User) {
      $this->logger->debug('User is not logged in.');

      return false;
    }

    assert($subject instanceof Ownable);
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
    $relationship = $this->relationshipRepository->findActiveRelationship($user, $owner, $capability->permissionsRequired());
    if ($relationship) {
      return true;
    }

    return false;
  }
}
