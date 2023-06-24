<?php

declare(strict_types=1);

use SimpleWebApps\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

header('Content-Type: text/plain');

$root = dirname(__DIR__).'/standby';
$online = dirname(__DIR__).'/online';

$zipPath = dirname(__DIR__).'/simplewebapps.zip';
if (file_exists($zipPath)) {
  // Remove all existing files
  array_map('unlink', glob($root.'/*'));

  // Extract the uploaded archive
  $zip = new ZipArchive();
  $res = $zip->open($zipPath);
  if (true !== $res) {
    http_response_code(500);
    exit('Failed to open zip: '.$res);
  }
  $res = $zip->extractTo($root);
  if (true !== $res) {
    http_response_code(500);
    exit('Failed to extract zip');
  }
  $zip->close();
  unlink($zipPath);

  // Server might time out, so redirect to keep it alive
  header("Location: {$_SERVER['REQUEST_URI']}");
  exit('Finished unzipping');
}

require_once $root.'/vendor/autoload_runtime.php';

return function () use ($root, $online) {
  $kernel = new Kernel('prod', true);
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
    $realStandby = $fs->readlink($root);
    assert($realOnline && $realStandby);

    $fs->symlink($realStandby, $online);
    $fs->symlink($realOnline, $root);
    $fs->remove('postdeploy.php');
  } catch (Exception $e) {
    $message = var_export($e, true);
    $output->writeln($message);

    return new Response($output->fetch(), 500);
  }

  return new Response($output->fetch());
};
