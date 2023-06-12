<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Dropzone\Form\DropzoneType;

trait EditImageMixin
{
  use AbstractControllerTrait;

  protected const ROUTE_EDIT_IMAGE_PATH = '/{id}/edit_image';

  public const ROUTE_EDIT_IMAGE_NAME = 'edit_image';

  protected function editImageModal(string $backUrl): Response
  {
    $form = $this->createFormBuilder()
      ->add('dropzone', DropzoneType::class, ['required' => false])
      ->add('image', HiddenType::class)
      ->getForm();

    return $this->render('modal/edit_image.html.twig', [
      'form' => $form,
      'back_url' => $backUrl,
    ]);
  }
}
