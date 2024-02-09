<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use SimpleWebApps\Auth\AuthenticatedUser;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\BookOwnershipRepository;
use SimpleWebApps\Repository\BookRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function assert;

#[AsLiveComponent('book:list')]
class BookList
{
  use DefaultActionTrait;

  /** @var User[] */
  public readonly array $users;

  #[LiveProp(writable: true)]
  public User $currentUser;

  #[LiveProp(writable: true)]
  public string $viewFilter = BookViewFilter::All->value;

  /** @var (BookViewFilter|BookOwnershipState)[] */
  public readonly array $allViewFilters;

  public function __construct(
    private BookRepository $bookRepository,
    private BookOwnershipRepository $bookOwnershipRepository,
    Security $security,
  ) {
    $authenticatedUser = $security->getUser();
    assert($authenticatedUser instanceof AuthenticatedUser);
    $this->currentUser = $authenticatedUser->user;

    $this->users = [
      $authenticatedUser->user,
      ...$authenticatedUser->iterateControlledUsers(RelationshipCapability::Read->permissionsRequired()),
    ];
    $this->allViewFilters = array_merge(BookOwnershipState::cases(), BookViewFilter::cases());
  }

  /**
   * @return Book[]
   */
  public function getPublicBooks(): array
  {
    return match (BookViewFilter::tryFrom($this->viewFilter)) {
      BookViewFilter::Public => $this->bookRepository->findPublicBooks(),
      BookViewFilter::PublicAbsent => $this->bookRepository->findBooksNotBelongingToUser($this->currentUser),
      default => [],
    };
  }

  /**
   * @return BookOwnership[]
   */
  public function getBookOwnerships(): array
  {
    $bookViewFilter = BookViewFilter::tryFrom($this->viewFilter);
    if (BookViewFilter::Public === $bookViewFilter || BookViewFilter::PublicAbsent === $bookViewFilter) {
      return [];
    }

    $ownershipState = BookOwnershipState::tryFrom($this->viewFilter);

    return $ownershipState
      ? $this->bookOwnershipRepository->findWithBookByOwnerAndState($this->currentUser, $ownershipState)
      : $this->bookOwnershipRepository->findWithBookByOwner($this->currentUser);
  }

  public function getListClasses(): array
  {
    return
      BookRenderingUtilities::privateListClasses($this->viewFilter, $this->currentUser)
      ?? BookRenderingUtilities::publicListClasses($this->viewFilter, $this->currentUser)
      ?? [];
  }
}
