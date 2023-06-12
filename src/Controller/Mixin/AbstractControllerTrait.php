<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller\Mixin;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

trait AbstractControllerTrait
{
  abstract protected function denyAccessUnlessGranted(mixed $attribute, mixed $subject = null, string $message = 'Access Denied.'): void;

  abstract protected function render(string $view, array $parameters = []): Response;

  abstract protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface;

  abstract protected static function getControllerShortName(): string;
}
