<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Form\BookOwnershipType;
use SimpleWebApps\Repository\BookOwnershipRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @extends AbstractCrudController<BookOwnership>
 */
#[Route('/books', name: self::CONTROLLER_SHORT_NAME)]
class BooksController extends AbstractCrudController
{
  protected const CONTROLLER_SHORT_NAME = 'books';
  protected const FORM_TYPE = BookOwnershipType::class;

  #[Route(self::ROUTE_INDEX_PATH, name: self::ROUTE_INDEX_NAME, methods: ['GET'])]
  public function index(): Response
  {
    return $this->render('books/index.html.twig');
  }

  #[Route(self::ROUTE_NEW_PATH, name: self::ROUTE_NEW_NAME, methods: ['GET', 'POST'])]
  public function new(Request $request, BookOwnershipRepository $bookOwnershipRepository): Response
  {
    return $this->crudNew($request, $bookOwnershipRepository, new BookOwnership());
  }
}
