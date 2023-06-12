<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Identifiable;
use SimpleWebApps\Repository\AbstractRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function is_string;

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

  public const ROUTE_INDEX_NAME = '_index';
  public const ROUTE_NEW_NAME = '_new';
  public const ROUTE_EDIT_NAME = '_edit';
  public const ROUTE_PREDELETE_NAME = '_pre_delete';
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
    array $extraButtons = []
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

    $id = $entity->getId();
    $parameters = [
        'id' => $id,
        'form' => $form,
        'subject' => self::getControllerShortName().self::SUBJECT_SUFFIX,
        'pre_delete_path' => $isDeletable ? $this->generateUrl(self::getControllerShortName().self::ROUTE_PREDELETE_NAME, ['id' => $id]) : null,
        'extra_buttons' => $extraButtons,
    ];

    return $this->render('modal/edit.html.twig', $parameters);
  }

  /**
   * @param T $entity
   */
  protected function crudPreDelete($entity, ?string $extraFooter = null): Response
  {
    $id = $entity->getId();

    return $this->render('modal/pre_delete.html.twig', [
        'id' => $id,
        'subject' => self::getControllerShortName().self::SUBJECT_SUFFIX,
        'delete_path' => $this->generateUrl(self::getControllerShortName().self::ROUTE_DELETE_NAME, ['id' => $id]),
        'extra_footer' => $extraFooter,
    ]);
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
    $token = $request->request->get('_token');
    assert(is_string($token) || null === $token);
    if ($this->isCsrfTokenValid('delete'.((string) $entity->getId()), $token)) {
      if ($entity instanceof Ownable) {
        $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $entity);
      }
      $repository->remove($entity, $flush);

      return true;
    }

    return false;
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
