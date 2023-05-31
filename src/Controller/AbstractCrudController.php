<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Identifiable;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Form\AbstractCrudType;
use SimpleWebApps\Repository\AbstractRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function is_string;

/**
 * @template E of Identifiable&Ownable
 */
abstract class AbstractCrudController extends AbstractController
{
  /** @var string */
  protected const CONTROLLER_SHORT_NAME = '***OVERRIDE ME***';
  /** @var class-string */
  protected const FORM_TYPE = self::class;

  protected const ROUTE_INDEX_PATH = '/';
  protected const ROUTE_NEW_PATH = '/new';
  protected const ROUTE_EDIT_PATH = '/{id}/edit';
  protected const ROUTE_DELETE_PATH = '/{id}/delete';

  protected const ROUTE_INDEX_NAME = '_index';
  protected const ROUTE_NEW_NAME = '_new';
  protected const ROUTE_EDIT_NAME = '_edit';
  protected const ROUTE_PREDELETE_NAME = '_pre_delete';
  protected const ROUTE_DELETE_NAME = '_delete';

  private const SUBJECT_SUFFIX = '.subject';

  /**
   * @param AbstractRepository<E> $repository
   * @param E                     $entity
   */
  protected function crudNew(Request $request, $repository, $entity): Response
  {
    $user = $this->getUser();
    assert($user instanceof User);
    $entity->setOwner($user);
    $form = $this->createNewEditForm($request, $entity);

    if ($form->isSubmitted() && $form->isValid()) {
      $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $entity);
      $repository->save($entity, true);

      return $this->closeModalOrRedirect($request);
    }

    return $this->render('modal/new.html.twig', [
        'form' => $form,
        'subject' => (string) $this::CONTROLLER_SHORT_NAME.self::SUBJECT_SUFFIX,
    ]);
  }

  /**
   * @param AbstractRepository<E> $repository
   * @param E                     $entity
   */
  protected function crudEdit(Request $request, $repository, $entity): Response
  {
    if (!$this->isGranted(RelationshipCapability::Write->value, $entity)) {
      return $this->render('modal/forbidden.html.twig', [
          'subject' => (string) $this::CONTROLLER_SHORT_NAME.self::SUBJECT_SUFFIX,
      ])
          ->setStatusCode(Response::HTTP_FORBIDDEN);
    }

    $form = $this->createNewEditForm($request, $entity);

    if ($form->isSubmitted() && $form->isValid()) {
      $repository->save($entity, true);

      return $this->closeModalOrRedirect($request);
    }

    $id = $entity->getId();

    return $this->render('modal/edit.html.twig', [
        'id' => $id,
        'form' => $form,
        'subject' => (string) $this::CONTROLLER_SHORT_NAME.self::SUBJECT_SUFFIX,
        'pre_delete_path' => $this->generateUrl((string) $this::CONTROLLER_SHORT_NAME.self::ROUTE_PREDELETE_NAME, ['id' => $id]),
    ]);
  }

  /**
   * @param E $entity
   */
  protected function crudPreDelete($entity): Response
  {
    $id = $entity->getId();

    return $this->render('modal/pre_delete.html.twig', [
        'id' => $id,
        'subject' => (string) $this::CONTROLLER_SHORT_NAME.self::SUBJECT_SUFFIX,
        'delete_path' => $this->generateUrl((string) $this::CONTROLLER_SHORT_NAME.self::ROUTE_DELETE_NAME, ['id' => $id]),
    ]);
  }

  /**
   * @param AbstractRepository<E> $repository
   * @param E                     $entity
   */
  protected function crudDelete(Request $request, $repository, $entity): Response
  {
    $token = $request->request->get('_token');
    assert(is_string($token) || null === $token);
    if ($this->isCsrfTokenValid('delete'.((string) $entity->getId()), $token)) {
      $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $entity);
      $repository->remove($entity, true);
    }

    return $this->closeModalOrRedirect($request);
  }

  private function closeModalOrRedirect(Request $request): Response
  {
    if ('app-modal' === $request->headers->get('Turbo-Frame')) {
      return $this->render('modal/close.html.twig');
    }

    return $this->redirectToRoute(
      (string) $this::CONTROLLER_SHORT_NAME.self::ROUTE_INDEX_NAME,
      status: Response::HTTP_SEE_OTHER
    );
  }

  /**
   * @param E $entity
   */
  private function createNewEditForm(
    Request $request,
    $entity,
  ): FormInterface {
    /**
     * TODO.
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    $form = $this->createForm((string) $this::FORM_TYPE, $entity, [
      AbstractCrudType::IS_OWNER_DISABLED => null !== $entity->getIdOrNull(),
    ]);
    $form->handleRequest($request);

    return $form;
  }
}
