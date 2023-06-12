<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use SimpleWebApps\Form\EditImageType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

trait EditImageMixin
{
  protected const ROUTE_EDIT_IMAGE_PATH = '/{id}/edit_image';

  public const ROUTE_EDIT_IMAGE_NAME = 'edit_image';

  abstract protected function render(string $view, array $parameters = []): Response;

  abstract protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface;

  protected function editImageModal(): Response
  {
    $form = $this->createForm(EditImageType::class);

    return $this->render('modal/edit_image.html.twig', [
      'form' => $form,
    ]);
  }
}
