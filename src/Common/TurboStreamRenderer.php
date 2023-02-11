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
    $targets = '.'.$class;
    $strippedPayload = str_replace("\n", '', $payload);

    return $this->twig
      ->createTemplate("<turbo-stream action=\"{$action->value}\" targets=\"$targets\"><template>$strippedPayload</template></turbo-stream>")
      ->render($context)
    ;
  }
}
