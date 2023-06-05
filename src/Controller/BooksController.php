<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Form\BookOwnershipType;
use SimpleWebApps\Repository\BookOwnershipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

  #[Route(self::ROUTE_NEW_PATH, name: self::ROUTE_NEW_NAME, methods: ['GET', 'POST'])]
  public function new(Request $request, BookOwnershipRepository $bookOwnershipRepository): Response
  {
    return $this->crudNewAndClose($request, $bookOwnershipRepository, (new BookOwnership())->setOwner($this->forceGetUser()));
  }

  #[Route(self::ROUTE_EDIT_PATH, name: self::ROUTE_EDIT_NAME, methods: ['GET', 'POST'])]
  public function edit(Request $request, BookOwnership $bookOwnership, BookOwnershipRepository $bookOwnershipRepository): Response
  {
    return $this->crudEdit($request, $bookOwnershipRepository, $bookOwnership);
  }

  #[Route(self::ROUTE_DELETE_PATH, name: self::ROUTE_PREDELETE_NAME, methods: ['POST'])]
  public function preDelete(BookOwnership $bookOwnership): Response
  {
    // TODO
    return new Response($bookOwnership->getBook()->getTitle());
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
