<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\BookOwnershipRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function assert;

#[AsLiveComponent('book:list')]
class BookList
{
  use DefaultActionTrait;

  /** @var Book[] */
  public array $books = [];

  public function __construct(Security $security, BookOwnershipRepository $bookOwnershipRepository)
  {
    $user = $security->getUser();
    assert($user instanceof User);
    foreach ($bookOwnershipRepository->findBy(['owner' => $user]) as $bookOwner) {
      $this->books[] = $bookOwner->getBook();
    }
  }
}
