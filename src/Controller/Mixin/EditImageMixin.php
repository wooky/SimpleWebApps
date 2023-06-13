<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use SimpleWebApps\Entity\Interface\Identifiable;
use SimpleWebApps\Entity\Interface\Imageable;
use SimpleWebApps\Repository\AbstractRepository;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Dropzone\Form\DropzoneType;

use function assert;

use const DIRECTORY_SEPARATOR;

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
   * @param AbstractRepository<T> $repository
   * @param T                     $entity
   */
  protected function editImageModal(
    Request $request,
    UploadableManager $uploadableManager,
    $repository,
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
      $uploadableManager->markEntityToUpload($entity, $image);
      $repository->save($entity, true);

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
    UploadableManager $uploadableManager,
    $repository,
    $entity,
  ): Response {
    if ($this->allowedToDelete($request, (string) $entity->getId())) {
      $imagePathPrefix = $uploadableManager->getUploadableListener()->getDefaultPath();
      $imagePathSuffix = $entity->getImagePath();
      assert(null !== $imagePathPrefix && null !== $imagePathSuffix);
      $imagePath = $imagePathPrefix.DIRECTORY_SEPARATOR.$imagePathSuffix;

      $entity->setImagePath(null);
      $repository->save($entity, true);
      $uploadableManager->getUploadableListener()->removeFile($imagePath);
    }

    return $this->closeModalOrRedirect($request);
  }
}
