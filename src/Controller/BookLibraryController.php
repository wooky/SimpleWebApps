<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Controller\Mixin\CrudMixin;
use SimpleWebApps\Controller\Mixin\EditImageMixin;
use SimpleWebApps\Entity\Book;
use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Form\BookType;
use SimpleWebApps\Repository\BookOwnershipRepository;
use SimpleWebApps\Repository\BookRepository;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
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
  /** @use EditImageMixin<Book> */
  use EditImageMixin;

  public const CONTROLLER_SHORT_NAME = 'book_library';

  #[Route(self::ROUTE_NEW_PATH, name: self::ROUTE_NEW_NAME, methods: ['GET', 'POST'])]
  public function new(
    Request $request,
    BookRepository $bookRepository,
    BookOwnershipRepository $bookOwnershipRepository,
  ): Response {
    $book = new Book();
    $response = $this->crudNewAndForm($request, $bookRepository, $book, flush: false);
    if ($response instanceof FormInterface) {
      $user = $response->get(BookType::OWNER_FIELD)->getData();
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

  #[Route(self::ROUTE_EDIT_PATH, name: self::ROUTE_EDIT_NAME, methods: ['GET', 'POST'])]
  public function edit(Request $request, Book $book, BookRepository $bookRepository): Response
  {
    return $this->crudEdit($request, $bookRepository, $book, isDeletable: false, extraButtons: [
      'edit_image.button' => $this->generateUrl(self::CONTROLLER_SHORT_NAME.self::ROUTE_EDIT_IMAGE_NAME, ['id' => $book->getId()]),
    ]);
  }

  #[Route(self::ROUTE_EDIT_IMAGE_PATH, name: self::ROUTE_EDIT_IMAGE_NAME, methods: ['GET', 'POST'])]
  public function editImage(Request $request, Book $book, BookRepository $bookRepository, UploadableManager $uploadableManager): Response
  {
    return $this->editImageModal(
      $request,
      $uploadableManager,
      $bookRepository,
      $book,
      null !== $book->getImagePath(),
      $this->generateUrl(self::CONTROLLER_SHORT_NAME.self::ROUTE_EDIT_NAME, ['id' => $book->getId()]),
    );
  }

  #[Route(self::ROUTE_DELETE_IMAGE_PATH, name: self::ROUTE_DELETE_IMAGE_NAME, methods: ['DELETE'])]
  public function deleteImage(): Response
  {
    return new Response();
  }

  /**
   * @param Book $entity
   */
  protected function createNewEditForm(Request $request, $entity): FormInterface
  {
    $form = $this->createForm(BookType::class, $entity, [
      BookType::ADD_OWNER_FIELD => null === $entity->getIdOrNull(),
      BookType::IS_PUBLIC_DISABLED => null !== $entity->getIdOrNull() && $entity->isPublic(),
    ]);
    $form->handleRequest($request);

    return $form;
  }

  protected static function getControllerShortName(): string
  {
    return self::CONTROLLER_SHORT_NAME;
  }
}
