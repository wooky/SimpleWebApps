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
  public readonly array $users;

  #[LiveProp(writable: true)]
  public User $currentUser;

  #[LiveProp(writable: true)]
  public string $viewFilter = BookViewFilter::All->value;

  /** @var BookOwnership[] */
  public array $bookOwnerships;

  /** @var Book[] */
  public array $publicBooks;

  /** @var (BookViewFilter|BookOwnershipState)[] */
  public readonly array $allViewFilters;

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

    $this->users = $userRepository->getControlledUsersIncludingSelf(
      [$user],
      RelationshipCapability::Read->permissionsRequired(),
    );
    $this->allViewFilters = array_merge(BookOwnershipState::cases(), BookViewFilter::cases());
  }

  #[LiveAction]
  public function refresh(): void
  {
    // FIXME need to verify user has permission to view selected user's books!

    $this->bookOwnerships = [];
    $this->publicBooks = [];

    match (BookViewFilter::tryFrom($this->viewFilter)) {
      BookViewFilter::Public => $this->publicBooks = $this->bookRepository->findPublicBooks(),
      BookViewFilter::PublicAbsent => $this->publicBooks =
        $this->bookRepository->findBooksNotBelongingToUser($this->currentUser),
      default => $this->queryUserBooks(),
    };
  }

  private function queryUserBooks(): void
  {
    $queryCriteria = [
      'owner' => $this->currentUser,
    ];
    $ownershipState = BookOwnershipState::tryFrom($this->viewFilter);
    if ($ownershipState) {
      $queryCriteria['state'] = $ownershipState;
    }

    $this->bookOwnerships = $this->bookOwnershipRepository->findBy($queryCriteria);
  }

  public function getListClasses(): array
  {
    return
      BookRenderingUtilities::privateListClasses($this->viewFilter, $this->currentUser)
      ?? BookRenderingUtilities::publicListClasses($this->viewFilter, $this->currentUser)
      ?? [];
  }
}
