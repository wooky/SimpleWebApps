<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\ListenersInvoker;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use SimpleWebApps\Entity\Artefact;
use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Entity\Interface\Imageable;
use SimpleWebApps\Repository\AbstractRepository;
use SimpleWebApps\Repository\ArtefactRepository;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Dropzone\Form\DropzoneType;

use function assert;

/**
 * @template T of Identifiable&Imageable
 */
trait EditImageMixin
{
  use AbstractControllerTrait;

  protected const ROUTE_EDIT_IMAGE_PATH = '/{id}/edit_image';
  protected const ROUTE_DELETE_IMAGE_PATH = '/{id}/delete_image';

  public const ROUTE_EDIT_IMAGE_NAME = 'edit_image';
  public const ROUTE_DELETE_IMAGE_NAME = 'delete_image';

  private const FORM_FIELD_IMAGE = 'image';

  /**
   * FIXME this entire method is a superfund site.
   *
   * @param T $entity
   */
  protected function editImageModal(
    Request $request,
    UploadableManager $uploadableManager,
    EntityManagerInterface $entityManager,
    ArtefactRepository $artefactRepository,
    $entity,
    bool $isDeletable,
    string $backUrl,
  ): Response {
    $form = $this->createFormBuilder()
      ->add(self::FORM_FIELD_IMAGE, DropzoneType::class)
      ->getForm();
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $image = $form->get(self::FORM_FIELD_IMAGE)->getData();
      assert($image instanceof UploadedFile);
      if ('image/jpeg' !== $image->getClientMimeType()) {
        // TODO
        return new Response('Image type must be a JPEG', Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      // TODO Doctrine throws a fit if we modify an artefact belonging to the entity, so the artefact needs to be queried separately.
      $artefact = $entity->getImage();
      if ($artefact) {
        $artefact = $artefactRepository->find($artefact->getId());
        assert($artefact instanceof Artefact);
      } else {
        $artefact = new Artefact();
        $entity->setImage($artefact);
        $entityManager->persist($entity);
      }

      $uploadableManager->markEntityToUpload($artefact, $image);
      $artefactRepository->save($artefact, true);

      // TODO the entity update event does not get called if the image gets modified, so we call the events here manually.
      $classMetadata = $entityManager->getClassMetadata($entity::class);
      $listenersInvoker = new ListenersInvoker($entityManager);
      $postUpdateInvoke = $listenersInvoker->getSubscribedSystems($classMetadata, Events::postUpdate);
      $listenersInvoker->invoke($classMetadata, Events::postUpdate, $entity, new PostUpdateEventArgs($entity, $entityManager), $postUpdateInvoke);

      return $this->closeModalOrRedirect($request);
    }

    $id = $entity->getId();

    return $this->render('modal/edit_image.html.twig', [
      'id' => $id,
      'form' => $form,
      'back_url' => $backUrl,
      'delete_path' => $isDeletable ? $this->generateUrl(self::getControllerShortName().self::ROUTE_DELETE_IMAGE_NAME, ['id' => $id]) : null,
    ]);
  }

  /**
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function handleDeleteImage(
    Request $request,
    ArtefactRepository $artefactRepository,
    $repository,
    $entity,
  ): Response {
    if ($this->allowedToDelete($request, (string) $entity->getId())) {
      $artefact = $entity->getImage();
      assert(null !== $artefact);
      $artefactRepository->remove($artefact);
      $entity->setImage(null);
      $repository->save($entity, true);
    }

    return $this->closeModalOrRedirect($request);
  }
}
