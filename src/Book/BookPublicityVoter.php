<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use Psr\Log\LoggerInterface;
use SimpleWebApps\Auth\AuthenticatedUser;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Book;
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
    $authenticatedUser = $token->getUser();
    if (!$authenticatedUser instanceof AuthenticatedUser) {
      $this->logger->debug('User is not logged in.', ['authenticatedUser' => $authenticatedUser]);

      return false;
    }

    if ($authenticatedUser->user === $subject->getCreator()) {
      return true;
    }
    if (BookPublicity::PublicCommunity === $subject->getPublicity()) {
      return true;
    }

    return $authenticatedUser->doesRelationshipExist(
      $subject->getCreator(),
      RelationshipCapability::Write->permissionsRequired(),
    );
  }
}
