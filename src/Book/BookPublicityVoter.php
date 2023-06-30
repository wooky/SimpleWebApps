<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Psr\Log\LoggerInterface;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\RelationshipRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string,Book>
 *
 * TODO
 *
 * @phan-file-suppress PhanUnusedProtectedFinalMethodParameter
 */
final class BookPublicityVoter extends Voter
{
  public function __construct(
    private readonly RelationshipRepository $relationshipRepository,
    private readonly LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  protected function supports(string $attribute, mixed $subject): bool
  {
    return $subject instanceof Book;
  }

  /**
   * @param Book $subject
   */
  protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
  {
    $user = $token->getUser();
    if (!$user instanceof User) {
      $this->logger->debug('User is not logged in.');

      return false;
    }

    if ($user === $subject->getCreator()) {
      return true;
    }
    if (BookPublicity::PublicCommunity === $subject->getPublicity()) {
      return true;
    }

    $relationship = $this->relationshipRepository->findActiveRelationship(
      $user,
      $subject->getCreator(),
      RelationshipCapability::Write->permissionsRequired(),
    );
    if ($relationship) {
      return true;
    }

    return false;
  }
}
