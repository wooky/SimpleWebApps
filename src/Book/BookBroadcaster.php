<?php

declare(strict_types=1);

namespace SimpleWebApps\Book;

use RuntimeException;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventBusInterface;
use SimpleWebApps\EventBus\EventScope;
use SimpleWebApps\Repository\BookOwnershipRepository;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Component\Uid\Ulid;
use Twig\Environment;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BookBroadcaster
{
  public const TOPIC = 'books';

  private const STREAM_TEMPLATE = 'books/stream.html.twig';

  public function __construct(
    private readonly UserRepository $userRepository,
    private readonly BookOwnershipRepository $bookOwnershipRepository,
    private readonly Environment $twig,
    private readonly EventBusInterface $eventBus,
  ) {
    // Do nothing.
  }

  public function onBookOwnershipCreated(BookOwnership $bookOwnership): void
  {
    $owner = $bookOwnership->getOwner() ?? throw new RuntimeException('BookOwnership has no owner');
    $template = $this->twig->load(self::STREAM_TEMPLATE);
    $content = $template->renderBlock('book_ownership_created', [
      'selector' => BookRenderingUtilities::composeQuerySelectorOfPrivateList($owner, $bookOwnership->getState()),
      'bookOwnership' => $bookOwnership,
    ]);
    $this->broadcastToAffectedUsers([$owner], $content);

    if ($bookOwnership->getBook()->isPublic()) {
      $content = $template->renderBlock('book_ownership_created', [
        'selector' => BookRenderingUtilities::composeQuerySelectorOfPublicListNotBelongingToUser($owner),
        'bookOwnership' => BookRenderingUtilities::wrapInEmptyOwnership($bookOwnership->getBook()),
      ]);
      $this->broadcastToAllUsers($content);
    }
  }

  public function onBookUpdated(Book $book): void
  {
    $content = $this->twig
      ->load(self::STREAM_TEMPLATE)
      ->renderBlock('book_updated', [
        'id' => BookRenderingUtilities::contentHtmlId($book->getId()),
        'bookOwnership' => BookRenderingUtilities::wrapInEmptyOwnership($book),
      ]);

    // Broadcast to all users if the book is public
    if ($book->isPublic()) {
      $this->broadcastToAllUsers($content);

      return;
    }

    // Broadcast to all users that have the book in their library, as well as other users that have a relationship with said users
    $bookOwners = array_map(
      fn (BookOwnership $bookOwnership) => $bookOwnership->getOwner() ?? throw new RuntimeException('Book ownership has no owner'),
      $this->bookOwnershipRepository->findBy(['book' => $book])
    );
    $this->broadcastToAffectedUsers($bookOwners, $content);
  }

  public function onBookDeleted(Book $book, Ulid $bookId): void
  {
    // Broadcast only if the book was public, since a private book card got automatically removed once the book ownership got removed.
    if ($book->isPublic()) {
      $content = $this->twig
        ->load(self::STREAM_TEMPLATE)
        ->renderBlock('book_deleted', [
          'id' => BookRenderingUtilities::cardHtmlId($bookId),
        ]);
      $this->broadcastToAllUsers($content);
    }
  }

  /**
   * @param User[] $users
   */
  private function broadcastToAffectedUsers(array $users, string $content): void
  {
    $affectedUsers = array_map(
      fn (User $user) => (string) $user->getId(),
      $this->userRepository->getControllingUsersIncludingSelf($users, RelationshipCapability::Read->permissionsRequired())
    );
    $this->eventBus->post(new Event($affectedUsers, self::TOPIC, $content));
  }

  private function broadcastToAllUsers(string $content): void
  {
    $this->eventBus->post(new Event([], self::TOPIC, $content, EventScope::AllUsersOfSpecifiedTopic));
  }
}
