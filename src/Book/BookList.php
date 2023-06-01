<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\BookOwnershipRepository;
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
  public ?User $currentUser;

  #[LiveProp(writable: true)]
  public ?BookOwnershipState $ownership = null;

  /** @var Book[] */
  public array $books;

  public function __construct(
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
    $queryCriteria = [
      'owner' => $this->currentUser,
    ];
    if ($this->ownership) {
      $queryCriteria['state'] = $this->ownership;
    }

    $this->books = [];
    foreach ($this->bookOwnershipRepository->findBy($queryCriteria) as $bookOwner) {
      $this->books[] = $bookOwner->getBook();
    }
  }

  public function getAllOwnershipStates(): array
  {
    return BookOwnershipState::cases();
  }
}
