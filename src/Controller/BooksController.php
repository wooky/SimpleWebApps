<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Controller\Mixin\CrudMixin;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Form\BookOwnershipType;
use SimpleWebApps\Repository\BookOwnershipRepository;
use SimpleWebApps\Repository\BookRepository;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function assert;

#[Route('/books', name: self::CONTROLLER_SHORT_NAME)]
class BooksController extends AbstractController
{
  /** @use CrudMixin<BookOwnership> */
  use CrudMixin;

  public const CONTROLLER_SHORT_NAME = 'books';

  #[Route(self::ROUTE_INDEX_PATH, name: self::ROUTE_INDEX_NAME, methods: ['GET'])]
  public function index(): Response
  {
    return $this->render('books/index.html.twig');
  }

  #[Route(self::ROUTE_NEW_PATH.'/{bookid}', name: self::ROUTE_NEW_NAME, methods: ['POST'])]
  public function new(
    Request $request,
    #[MapEntity(id: 'bookid')] Book $book,
    UserRepository $userRepository,
    BookOwnershipRepository $bookOwnershipRepository,
  ): Response {
    $ownerId = $request->request->get('ownerid');
    assert(null !== $ownerId && !empty($ownerId));
    $owner = $userRepository->find($ownerId);
    assert(null !== $owner);

    $entity = (new BookOwnership())
      ->setOwner($owner)
      ->setBook($book)
    ;

    return $this->crudNewAndClose($request, $bookOwnershipRepository, $entity);
  }

  #[Route(self::ROUTE_EDIT_PATH, name: self::ROUTE_EDIT_NAME, methods: ['GET', 'POST'])]
  #[IsGranted(RelationshipCapability::Write->value, 'bookOwnership', message: self::CONTROLLER_SHORT_NAME)]
  public function edit(
    Request $request,
    BookOwnership $bookOwnership,
    BookOwnershipRepository $bookOwnershipRepository,
  ): Response {
    $bookOwners = $bookOwnershipRepository->count(['book' => $bookOwnership->getBook()]);
    $deleteWarning = (1 === $bookOwners) ? 'books.no_more_owners' : null;

    return $this->crudEdit($request, $bookOwnershipRepository, $bookOwnership, deleteWarning: $deleteWarning);
  }

  #[Route(self::ROUTE_DELETE_PATH, name: self::ROUTE_DELETE_NAME, methods: ['DELETE'])]
  #[IsGranted(RelationshipCapability::Write->value, 'bookOwnership', message: self::CONTROLLER_SHORT_NAME)]
  public function delete(
    Request $request,
    BookOwnership $bookOwnership,
    BookOwnershipRepository $bookOwnershipRepository,
    BookRepository $bookRepository,
  ): Response {
    $book = $bookOwnership->getBook();
    $soleBookOwner = 1 === $bookOwnershipRepository->count(['book' => $book]);
    $success = $this->crudDeleteAndTrue($request, $bookOwnershipRepository, $bookOwnership, flush: !$soleBookOwner);
    if ($success && $soleBookOwner) {
      $bookRepository->remove($book, true);
    }

    return $this->closeModalOrRedirect($request);
  }

  protected function createNewEditForm(Request $request, $entity): FormInterface
  {
    $form = $this->createForm(BookOwnershipType::class, $entity, [
      BookOwnershipType::IS_OWNER_DISABLED => null !== $entity->getIdOrNull(),
    ]);
    $form->handleRequest($request);

    return $form;
  }

  protected static function getControllerShortName(): string
  {
    return self::CONTROLLER_SHORT_NAME;
  }
}
