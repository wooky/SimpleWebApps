<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Identifiable;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\AbstractRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

use function assert;
use function is_string;

/**
 * @template T of Identifiable
 */
trait CrudMixin
{
  protected const ROUTE_INDEX_PATH = '/';
  protected const ROUTE_NEW_PATH = '/new';
  protected const ROUTE_EDIT_PATH = '/{id}/edit';
  protected const ROUTE_DELETE_PATH = '/{id}/delete';

  public const ROUTE_INDEX_NAME = '_index';
  public const ROUTE_NEW_NAME = '_new';
  public const ROUTE_EDIT_NAME = '_edit';
  public const ROUTE_PREDELETE_NAME = '_pre_delete';
  public const ROUTE_DELETE_NAME = '_delete';

  private const SUBJECT_SUFFIX = '.subject';

  abstract protected function getUser(): ?UserInterface;

  abstract protected function denyAccessUnlessGranted(mixed $attribute, mixed $subject = null, string $message = 'Access Denied.'): void;

  abstract protected static function getControllerShortName(): string;

  /**
   * @param T $entity
   */
  abstract protected function createNewEditForm(Request $request, $entity): FormInterface;

  protected function forceGetUser(): User
  {
    $user = $this->getUser();
    assert($user instanceof User);

    return $user;
  }

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function crudNewAndClose(Request $request, $repository, $entity): Response
  {
    $response = $this->crudNewAndTrue($request, $repository, $entity);

    return (true === $response) ? $this->closeModalOrRedirect($request) : $response;
  }

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function crudNewAndTrue(Request $request, $repository, $entity, bool $flush = true): Response|true
  {
    $form = $this->createNewEditForm($request, $entity);

    if ($form->isSubmitted() && $form->isValid()) {
      if ($entity instanceof Ownable) {
        $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $entity);
      }
      $repository->save($entity, $flush);

      return true;
    }

    return $this->render('modal/new.html.twig', [
        'form' => $form,
        'subject' => self::getControllerShortName().self::SUBJECT_SUFFIX,
    ]);
  }

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function crudEdit(Request $request, $repository, $entity): Response
  {
    if ($entity instanceof Ownable && !$this->isGranted(RelationshipCapability::Write->value, $entity)) {
      return $this->render('modal/forbidden.html.twig', [
        'subject' => self::getControllerShortName().self::SUBJECT_SUFFIX,
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
        'subject' => self::getControllerShortName().self::SUBJECT_SUFFIX,
        'pre_delete_path' => $this->generateUrl(self::getControllerShortName().self::ROUTE_PREDELETE_NAME, ['id' => $id]),
    ]);
  }

  /**
   * @param T $entity
   */
  protected function crudPreDelete($entity): Response
  {
    $id = $entity->getId();

    return $this->render('modal/pre_delete.html.twig', [
        'id' => $id,
        'subject' => self::getControllerShortName().self::SUBJECT_SUFFIX,
        'delete_path' => $this->generateUrl(self::getControllerShortName().self::ROUTE_DELETE_NAME, ['id' => $id]),
    ]);
  }

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function crudDelete(Request $request, $repository, $entity): Response
  {
    $token = $request->request->get('_token');
    assert(is_string($token) || null === $token);
    if ($this->isCsrfTokenValid('delete'.((string) $entity->getId()), $token)) {
      if ($entity instanceof Ownable) {
        $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $entity);
      }
      $repository->remove($entity, true);
    }

    return $this->closeModalOrRedirect($request);
  }

  protected function closeModalOrRedirect(Request $request): Response
  {
    if ('app-modal' === $request->headers->get('Turbo-Frame')) {
      return $this->render('modal/close.html.twig');
    }

    return $this->redirectToRoute(
      self::getControllerShortName().self::ROUTE_INDEX_NAME,
      status: Response::HTTP_SEE_OTHER
    );
  }
}
