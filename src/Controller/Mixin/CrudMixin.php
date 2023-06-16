<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Repository\AbstractRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @template T of Identifiable
 */
trait CrudMixin
{
  use AbstractControllerTrait;

  protected const ROUTE_INDEX_PATH = '/';
  protected const ROUTE_NEW_PATH = '/new';
  protected const ROUTE_EDIT_PATH = '/{id}/edit';
  protected const ROUTE_DELETE_PATH = '/{id}/delete';

  public const ROUTE_NEW_NAME = '_new';
  public const ROUTE_EDIT_NAME = '_edit';
  public const ROUTE_DELETE_NAME = '_delete';

  private const SUBJECT_SUFFIX = '.subject';

  /**
   * @param T $entity
   */
  abstract protected function createNewEditForm(Request $request, $entity): FormInterface;

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function crudNewAndClose(Request $request, $repository, $entity): Response
  {
    $response = $this->crudNewAndForm($request, $repository, $entity);

    return ($response instanceof Response) ? $response : $this->closeModalOrRedirect($request);
  }

  /**
   * Return form on success, response on failure. TODO wtf why am I making this hack why oh why.
   *
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function crudNewAndForm(Request $request, $repository, $entity, bool $flush = true): Response|FormInterface
  {
    $form = $this->createNewEditForm($request, $entity);

    if ($form->isSubmitted() && $form->isValid()) {
      if ($entity instanceof Ownable) {
        $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $entity);
      }
      $repository->save($entity, $flush);

      return $form;
    }

    return $this->render('modal/new.html.twig', [
        'form' => $form,
        'subject' => self::getControllerShortName().self::SUBJECT_SUFFIX,
    ]);
  }

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   * @param array<string,string>  $extraButtons
   */
  protected function crudEdit(
    Request $request,
    $repository,
    $entity,
    bool $isDeletable = true,
    array $extraButtons = [],
    ?string $deleteWarning = null,
  ): Response {
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

    // TODO phan can't cope with templates and instanceof @phan-suppress-next-line PhanUndeclaredMethod
    $id = $entity->getId();
    $parameters = [
        'id' => $id,
        'form' => $form,
        'subject' => self::getControllerShortName().self::SUBJECT_SUFFIX,
        'delete_path' => $isDeletable
          ? $this->generateUrl(self::getControllerShortName().self::ROUTE_DELETE_NAME, ['id' => $id])
          : null,
        'extra_buttons' => $extraButtons,
        'delete_warning' => $deleteWarning,
    ];

    return $this->render('modal/edit.html.twig', $parameters);
  }

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function crudDelete(Request $request, $repository, $entity): Response
  {
    $this->crudDeleteAndTrue($request, $repository, $entity);

    return $this->closeModalOrRedirect($request);
  }

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function crudDeleteAndTrue(Request $request, $repository, $entity, bool $flush = true): bool
  {
    if ($this->allowedToDelete($request, (string) $entity->getId())) {
      if ($entity instanceof Ownable) {
        $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $entity);
      }
      $repository->remove($entity, $flush);

      return true;
    }

    return false;
  }
}
