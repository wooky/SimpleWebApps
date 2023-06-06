<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\BookOwnershipRepository;
use SimpleWebApps\Repository\BookRepository;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function assert;

#[AsLiveComponent('book:list')]
class BookList
{
  use DefaultActionTrait;

  /** @var User[] */
  public array $users;

  #[LiveProp(writable: true)]
  public User $currentUser;

  #[LiveProp(writable: true)]
  public BookViewFilter $viewFilter = BookViewFilter::All;

  /** @var BookOwnership[] */
  public array $bookOwnerships;

  public function __construct(
    private BookRepository $bookRepository,
    private BookOwnershipRepository $bookOwnershipRepository,
    UserRepository $userRepository,
    Security $security,
  ) {
    $user = $security->getUser();
    assert($user instanceof User);
    $this->currentUser = $user;
    $this->refresh();

    $this->users = $userRepository->getControlledUsersIncludingSelf($user, RelationshipCapability::Read->permissionsRequired());
  }

  #[LiveAction]
  public function refresh(): void
  {
    // FIXME need to verify user has permission to view selected user's books!

    $this->bookOwnerships = match ($this->viewFilter) {
      BookViewFilter::Public => $this->wrapInEmptyOwnerships($this->bookRepository->findBy(['isPublic' => true])),
      BookViewFilter::PublicAbsent => $this->wrapInEmptyOwnerships($this->bookRepository->findBooksNotBelongingToUser($this->currentUser)),
      default => $this->queryUserBooks()
    };
  }

  /**
   * TODO.
   *
   * @param Book[] $books
   *
   * @return BookOwnership[]
   */
  private function wrapInEmptyOwnerships(array $books): array
  {
    $bookOwnerships = [];
    foreach ($books as $book) {
      $bookOwnerships[] = (new BookOwnership())->setBook($book);
    }

    return $bookOwnerships;
  }

  /**
   * @return BookOwnership[]
   */
  private function queryUserBooks(): array
  {
    $queryCriteria = [
      'owner' => $this->currentUser,
    ];
    $ownershipState = $this->viewFilter->toOwnershipState();
    if ($ownershipState) {
      $queryCriteria['state'] = $ownershipState;
    }

    return $this->bookOwnershipRepository->findBy($queryCriteria);
  }

  public function getViewFilters(): array
  {
    return BookViewFilter::cases();
  }
}
