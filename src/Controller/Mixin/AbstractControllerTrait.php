<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function is_string;

trait AbstractControllerTrait
{
  public const ROUTE_INDEX_NAME = '_index';

  abstract protected function denyAccessUnlessGranted(
    mixed $attribute,
    mixed $subject = null,
    string $message = 'Access Denied.',
  ): void;

  abstract protected function render(string $view, array $parameters = []): Response;

  abstract protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface;

  abstract protected function createFormBuilder(mixed $data = null, array $options = []): FormBuilderInterface;

  abstract protected static function getControllerShortName(): string;

  abstract protected function generateUrl(string $route, array $parameters = []): string;

  abstract protected function redirectToRoute(
    string $route,
    array $parameters = [],
    int $status = 302,
  ): RedirectResponse;

  abstract protected function isCsrfTokenValid(string $id, ?string $token): bool;

  abstract protected function isGranted(mixed $attribute, mixed $subject = null): bool;

  protected function closeModalOrRedirect(Request $request): Response
  {
    if ('app-modal' === $request->headers->get('Turbo-Frame')) {
      return $this->render('modal/close.html.twig');
    }

    return $this->redirectToRoute(
      self::getControllerShortName().self::ROUTE_INDEX_NAME,
      status: Response::HTTP_SEE_OTHER,
    );
  }

  protected function allowedToDelete(Request $request, string $id): bool
  {
    $token = $request->request->get('_token');
    assert(is_string($token) || null === $token);

    return $this->isCsrfTokenValid('delete'.$id, $token);
  }
}
