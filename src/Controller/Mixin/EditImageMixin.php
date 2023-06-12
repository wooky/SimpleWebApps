<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use Symfony\Component\HttpFoundation\Response;

trait EditImageMixin
{
  protected const ROUTE_EDIT_IMAGE_PATH = '/{id}/edit_image';

  public const ROUTE_EDIT_IMAGE_NAME = 'edit_image';

  protected function editImageModal(): Response
  {
    return $this->render('modal/edit_image.html.twig');
  }
}
