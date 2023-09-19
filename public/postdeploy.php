<?php

declare(strict_types=1);

use SimpleWebApps\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

header('Content-Type: text/plain');

$standby = dirname(__DIR__).'/standby';
$online = dirname(__DIR__).'/online';
$zipPath = dirname(__DIR__).'/simplewebapps.zip';

const TIMEOUT_NS = 55_000_000_000;
const PARAM_CONT = 'cont';

$timer = hrtime(true);

if (file_exists($zipPath)) {
  // Remove all existing files if continue parameter is unset
  if (isset($_GET[PARAM_CONT])) {
    /** @psalm-suppress RiskyCast */
    $fn = (int) $_GET[PARAM_CONT];
  } else {
    /** @psalm-suppress UnusedFunctionCall */ array_map('unlink', glob($standby.'/*'));
    $fn = 0;
  }

  // Extract the uploaded archive, file-by-file
  $zip = new ZipArchive();
  $res = $zip->open($zipPath);
  if (true !== $res) {
    http_response_code(500);
    exit('Failed to open zip: '.(string) $res);
  }

  for (; $fn < $zip->count(); ++$fn) {
    // If we're past the timeout, redirect to keep alive
    if (hrtime(true) - $timer >= TIMEOUT_NS) {
      /** @psalm-suppress PossiblyUndefinedArrayOffset */
      $uri = sprintf('%s?%s=%d', trim(strtok($_SERVER['REQUEST_URI'], '?')), PARAM_CONT, $fn);
      header("Location: $uri");
      exit('Unzipping paused before file '.(string) $fn);
    }

    $name = $zip->getNameIndex($fn);
    $res = $zip->extractTo($standby, $name);
    if (true !== $res) {
      http_response_code(500);
      exit('Failed to extract zip');
    }
  }

  // Server might time out, so redirect to keep it alive
  $zip->close();
  unlink($zipPath);
  /** @psalm-suppress PossiblyUndefinedArrayOffset */
  $uri = trim(strtok($_SERVER['REQUEST_URI'], '?'));
  header("Location: $uri");
  exit('Finished unzipping');
}

/** @psalm-suppress MissingFile */
require_once $standby.'/vendor/autoload_runtime.php';

return static function () use ($standby, $online) {
  $kernel = new Kernel('prod', false);
  $application = new Application($kernel);
  $application->setAutoExit(false);

  $commands = [
    ['command' => 'doctrine:database:create', '-n' => true, '--if-not-exists' => true],
    ['command' => 'doctrine:migrations:migrate', '-n' => true],
    ['command' => 'cache:clear'],
  ];

  $output = new BufferedOutput(BufferedOutput::VERBOSITY_DEBUG);
  $fs = new Filesystem();
  try {
    foreach ($commands as $i => $command) {
      $res = $application->run(new ArrayInput($command), $output);
      if (0 !== $res) {
        throw new Exception("Command $i failed with error code $res");
      }
    }
  } catch (Exception $e) {
    $message = var_export($e, true);
    $output->writeln($message);

    return new Response($output->fetch(), 500);
  }

  try {
    $realOnline = $fs->readlink($online);
    $realStandby = $fs->readlink($standby);
    assert($realOnline && $realStandby);

    $fs->symlink($realStandby, $online);
    $fs->symlink($realOnline, $standby);
    $fs->remove('postdeploy.php');
  } catch (Exception $e) {
    $message = var_export($e, true);
    $output->writeln($message);

    return new Response($output->fetch(), 500);
  }

  return new Response($output->fetch());
};
