<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Form\BookType;
use SimpleWebApps\Repository\BookOwnershipRepository;
use SimpleWebApps\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function assert;

#[Route('/books/library', name: self::CONTROLLER_SHORT_NAME)]
class BookLibraryController extends AbstractController
{
  /** @use CrudMixin<Book> */
  use CrudMixin;

  public const CONTROLLER_SHORT_NAME = 'book_library';

  #[Route(self::ROUTE_NEW_PATH, name: self::ROUTE_NEW_NAME, methods: ['GET', 'POST'])]
  public function new(
    Request $request,
    BookRepository $bookRepository,
    BookOwnershipRepository $bookOwnershipRepository,
  ): Response {
    $book = new Book();
    $response = $this->crudNewAndTrue($request, $bookRepository, $book, false);
    if (true === $response) {
      // TODO optimize
      $form = $this->createNewEditForm($request, $book);
      $user = $form->get(BookType::OWNER_FIELD)->getData();
      assert($user instanceof User);
      $bookOwnership = (new BookOwnership())
        ->setBook($book)
        ->setOwner($user)
      ;
      $bookOwnershipRepository->save($bookOwnership, true);

      return $this->redirectToRoute(BooksController::CONTROLLER_SHORT_NAME.BooksController::ROUTE_EDIT_NAME, ['id' => $bookOwnership->getId()]);
    }

    return $response;
  }

  protected function createNewEditForm(Request $request, $entity): FormInterface
  {
    $form = $this->createForm(BookType::class, $entity);
    $form->handleRequest($request);

    return $form;
  }

  protected static function getControllerShortName(): string
  {
    return self::CONTROLLER_SHORT_NAME;
  }
}
