<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait AbstractControllerTrait
{
  public const ROUTE_INDEX_NAME = '_index';

  abstract protected function denyAccessUnlessGranted(mixed $attribute, mixed $subject = null, string $message = 'Access Denied.'): void;

  abstract protected function render(string $view, array $parameters = []): Response;

  abstract protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface;

  abstract protected function createFormBuilder(mixed $data = null, array $options = []): FormBuilderInterface;

  abstract protected static function getControllerShortName(): string;

  abstract protected function generateUrl(string $route, array $parameters = []): string;

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
