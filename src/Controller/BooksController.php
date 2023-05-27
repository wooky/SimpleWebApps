<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Entity\BookOwnership;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Form\BookOwnershipType;
use SimpleWebApps\Repository\BookOwnershipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/books', name: 'books_')]
class BooksController extends AbstractController
{
  #[Route('/', name: 'index', methods: ['GET'])]
  public function index(): Response
  {
    return $this->render('books/index.html.twig');
  }

  #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
  public function new(Request $request, BookOwnershipRepository $bookOwnershipRepository): Response
  {
    $user = $this->getUser();
    assert($user instanceof User);
    $bookOwnership = (new BookOwnership())->setOwner($user);
    $form = $this->createNewEditForm($request, $bookOwnership);

    if ($form->isSubmitted() && $form->isValid()) {
      // $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $weightRecord);
      $bookOwnershipRepository->save($bookOwnership, true);

      return $this->closeModalOrRedirect($request, 'books_index');
    }

    return $this->render('modal/new.html.twig', [
        'form' => $form,
        'subject' => 'books.subject',
    ]);
  }

  private function closeModalOrRedirect(Request $request, string $route): Response
  {
    if ('app-modal' === $request->headers->get('Turbo-Frame')) {
      return $this->render('modal/close.html.twig');
    }

    return $this->redirectToRoute($route, status: Response::HTTP_SEE_OTHER);
  }

  private function createNewEditForm(
    Request $request,
    BookOwnership $bookOwnership,
  ): FormInterface {
    $form = $this->createForm(BookOwnershipType::class, $bookOwnership, [
        // WeightRecordType::IS_OWNER_DISABLED => null !== $weightRecord->getIdOrNull(),
    ]);
    $form->handleRequest($request);

    return $form;
  }
}
