<?php

declare(strict_types=1);

namespace SimpleWebApps\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Psr\Log\LoggerInterface;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use function array_slice;
use function assert;

/**
 * @implements UserProviderInterface<AuthenticatedUser>
 */
class AuthenticatedUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
  public function __construct(private EntityManagerInterface $entityManager, private LoggerInterface $logger)
  {
    // Do nothing.
  }

  public function loadUserByIdentifier(string $identifier): AuthenticatedUser
  {
    $qb = $this->entityManager->createQueryBuilder();

    $rows = $qb
      ->select('u', 'rel', 'tu')
      ->from(User::class, 'u')
      ->leftJoin(
        Relationship::class,
        'rel',
        Expr\Join::WITH,
        $qb->expr()->andX($qb->expr()->eq('rel.fromUser', 'u.id'), $qb->expr()->eq('rel.active', true)),
      )
      ->leftJoin(User::class, 'tu', Expr\Join::WITH, $qb->expr()->eq('tu.id', 'rel.toUser'))
      ->where($qb->expr()->eq('u.username', '?1'))
      ->setParameter(1, $identifier)
      ->getQuery()
      ->getResult();

    if (empty($rows)) {
      throw new UserNotFoundException();
    }

    // $rows[0] is the User, the rest are relationships
    assert(isset($rows[0]));
    $user = $rows[0];
    $fromRelationships = array_filter(
      array_slice($rows, 1),
      static fn ($relationship) => $relationship instanceof Relationship,
    );
    assert($user instanceof User);

    return new AuthenticatedUser($user, $fromRelationships); // @phan-suppress-current-line PhanTypeMismatchReturn
  }

  public function refreshUser(UserInterface $user): AuthenticatedUser
  {
    if (!$user instanceof AuthenticatedUser) {
      throw new UnsupportedUserException();
    }

    // @phan-suppress-next-line PhanTypeMismatchReturnSuperType
    return $this->loadUserByIdentifier($user->getUserIdentifier());
  }

  public function supportsClass(string $class): bool
  {
    return AuthenticatedUser::class === $class || is_subclass_of($class, AuthenticatedUser::class);
  }

  public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
  {
    if (!$user instanceof AuthenticatedUser) {
      return;
    }

    $user->user->setPassword($newHashedPassword);
    $this->entityManager->persist($user->user);
    $this->entityManager->flush();
  }
}
