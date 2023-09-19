<?php

declare(strict_types=1);

use SimpleWebApps\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

/** @psalm-suppress all */
return static function (array $context) {
  return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
