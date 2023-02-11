<?php

declare(strict_types=1);

namespace SimpleWebApps\Common;

use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Twig\Environment;

class TurboStreamRenderer
{
  public const MESSAGE = 'message';

  public function __construct(
    private Environment $twig,
    private ComponentRendererInterface $componentRenderer,
  ) {
    // Do nothing.
  }

  public function renderTwigComponentClass(TurboStreamAction $action, string $class, string $component, array $context): string
  {
    return $this->renderClass($action, $class, $this->componentRenderer->createAndRender($component, $context), []);
  }

  public function renderClass(TurboStreamAction $action, string $class, string $payload, array $context): string
  {
    $attributes = "targets=\".$class\"";

    return $this->render($action, $attributes, $payload, $context);
  }

  public function renderTwigComponentId(TurboStreamAction $action, string $target, string $component, array $context): string
  {
    return $this->renderId($action, $target, $this->componentRenderer->createAndRender($component, $context), []);
  }

  public function renderId(TurboStreamAction $action, string $target, string $payload, array $context): string
  {
    $attributes = "target=\"$target\"";

    return $this->render($action, $attributes, $payload, $context);
  }

  private function render(TurboStreamAction $action, string $attributes, string $payload, array $context): string
  {
    $strippedPayload = str_replace("\n", '', $payload);

    return $this->twig
      ->createTemplate("<turbo-stream action=\"{$action->value}\" $attributes><template>$strippedPayload</template></turbo-stream>")
      ->render($context)
    ;
  }
}
