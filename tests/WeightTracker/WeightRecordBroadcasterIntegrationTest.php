<?php

declare(strict_types=1);

use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\WeightTracker\WeightRecordBroadcaster;
use SimpleWebApps\WeightTracker\WeightTrackerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\Turbo\Broadcaster\BroadcasterInterface;

class WeightRecordBroadcasterIntegrationTest extends KernelTestCase
{
  private WeightRecordBroadcaster $weightRecordBroadcaster;
  private MockObject&BroadcasterInterface $mockBroadcaster;
  private LifecycleEventArgs $mockEvent;
  private User $master;
  private User $slaveRead;

  protected function setUp(): void
  {
    self::bootKernel();
    $container = self::getContainer();

    $this->mockBroadcaster = $this->createMock(BroadcasterInterface::class);
    $container->set(BroadcasterInterface::class, $this->mockBroadcaster);

    $mockWeightTrackerService = $this->createStub(WeightTrackerService::class);
    $mockWeightTrackerService->method('getRenderableDataSets')->willReturnCallback(fn (User $user) => [$user->getUsername()]);
    $container->set(WeightTrackerService::class, $mockWeightTrackerService);

    $this->weightRecordBroadcaster = $container->get(WeightRecordBroadcaster::class);
    $this->mockEvent = $this->createStub(LifecycleEventArgs::class);

/** @var UserRepository */ $userRepository = $container->get(UserRepository::class);
    $this->master = $userRepository->findOneBy(['username' => 'master']);
    $this->slaveRead = $userRepository->findOneBy(['username' => 'slave-read']);
  }

  public function testOnWeightRecordChange(): void
  {
    $weightRecord = (new WeightRecord())->setOwner($this->slaveRead);
    $this->assertBroadcasted($this->master, $this->slaveRead);
    $this->weightRecordBroadcaster->onWeightRecordChange($weightRecord, $this->mockEvent);
  }

  public function testOnRelationshipChange(): void
  {
    $relationship = (new Relationship())->setFromUser($this->master);
    $this->assertBroadcasted($this->master);
    $this->weightRecordBroadcaster->onRelationshipChange($relationship, $this->mockEvent);
  }

  private function assertBroadcasted(User ...$users): void
  {
    $arguments = array_map(fn (User $user) => [
      static::anything(),
      static::anything(),
      static::equalTo([
        'topics' => WeightRecordBroadcaster::getTopics($user),
        'rendered_action' => json_encode([$user->getUsername()]),
        'private' => true,
      ]),
    ], $users);
    $this->mockBroadcaster->expects(static::exactly(count($arguments)))->method('broadcast')->withConsecutive(...$arguments);
  }
}
