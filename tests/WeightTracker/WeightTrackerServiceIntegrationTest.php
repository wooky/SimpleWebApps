<?php

declare(strict_types=1);

use SimpleWebApps\DataFixtures\AppFixtures;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\WeightTracker\WeightTrackerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WeightTrackerServiceIntegrationTest extends KernelTestCase
{
  private WeightTrackerService $service;
  private UserRepository $userRepository;

  protected function setUp(): void
  {
    self::bootKernel();
    $container = self::getContainer();
    $this->service = $container->get(WeightTrackerService::class);
    $this->userRepository = $container->get(UserRepository::class);
  }

  public function testGetRenderableDataSets()
  {
    $master = $this->userRepository->findOneBy(['username' => 'master']);
    $slaveRead = $this->userRepository->findOneBy(['username' => 'slave-read']);
    $slaveWrite = $this->userRepository->findOneBy(['username' => 'slave-write']);

    $dataSets = $this->service->getRenderableDataSets($master);
    static::assertSame(3, count($dataSets));

    static::assertSame($master->getUsername(), $dataSets[0]->label);
    static::assertSame(AppFixtures::WEIGHT_RECORDS_PER_USER, count($dataSets[0]->data));
    static::assertTrue($dataSets[0]->__self);

    static::assertSame($slaveRead->getUsername(), $dataSets[1]->label);
    static::assertSame(AppFixtures::WEIGHT_RECORDS_PER_USER, count($dataSets[1]->data));
    static::assertFalse($dataSets[1]->__self);

    static::assertSame($slaveWrite->getUsername(), $dataSets[2]->label);
    static::assertSame(AppFixtures::WEIGHT_RECORDS_PER_USER, count($dataSets[2]->data));
    static::assertFalse($dataSets[2]->__self);
  }
}
