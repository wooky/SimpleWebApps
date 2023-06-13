<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use SimpleWebApps\Repository\AbstractRepository;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Dropzone\Form\DropzoneType;

use function assert;

/**
 * @template T of object
 */
trait EditImageMixin
{
  use AbstractControllerTrait;

  protected const ROUTE_EDIT_IMAGE_PATH = '/{id}/edit_image';

  public const ROUTE_EDIT_IMAGE_NAME = 'edit_image';

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
    string $backUrl
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
    }

    return $this->render('modal/edit_image.html.twig', [
      'form' => $form,
      'back_url' => $backUrl,
    ]);
  }
}
