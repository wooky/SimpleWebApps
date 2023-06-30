<?php

declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;

header('Content-Type: text/plain');

$standby = dirname(__DIR__).'/standby';
$online = dirname(__DIR__).'/online';

/** @psalm-suppress MissingFile */
require_once $online.'/vendor/autoload_runtime.php';

return function () use ($standby, $online) {
  $fs = new Filesystem();
  $realOnline = $fs->readlink($online);
  $realStandby = $fs->readlink($standby);
  assert($realOnline && $realStandby);

  $fs->symlink($realStandby, $online);
  $fs->symlink($realOnline, $standby);
  $fs->remove('rollback.php');
};
