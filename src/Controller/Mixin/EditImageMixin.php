<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use SimpleWebApps\Form\EditImageType;
use Symfony\Component\HttpFoundation\Response;

trait EditImageMixin
{
  use AbstractControllerTrait;

  protected const ROUTE_EDIT_IMAGE_PATH = '/{id}/edit_image';

  public const ROUTE_EDIT_IMAGE_NAME = 'edit_image';

  protected function editImageModal(string $backUrl): Response
  {
    $form = $this->createForm(EditImageType::class);

    return $this->render('modal/edit_image.html.twig', [
      'form' => $form,
      'back_url' => $backUrl,
    ]);
  }
}
