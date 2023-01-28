<?php

declare(strict_types=1);

namespace SimpleWebApps\DataFixtures;

use function assert;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class AppFixtures extends Fixture
{
  public const WEIGHT_RECORDS_PER_USER = 31;

  public function __construct(
    private readonly UserPasswordHasherInterface $userPasswordHasher,
  ) {
    // Do nothing.
  }

  public function load(ObjectManager $manager): void
  {
    // Disable event listeners
    $metadata = $manager->getMetadataFactory()->getAllMetadata();
    foreach ($metadata as $metadatum) {
      assert($metadatum instanceof ClassMetadata);
      $metadatum->entityListeners = [];
    }

    $master = $this->createUser($manager, 'master');
    $slaveRead = $this->createUser($manager, 'slave-read');
    $slaveWrite = $this->createUser($manager, 'slave-write');
    $slaveReadPending = $this->createUser($manager, 'slave-read-pending');
    $slaveWritePending = $this->createUser($manager, 'slave-write-pending');
    $subSlave = $this->createUser($manager, 'sub-slave');
    $loner = $this->createUser($manager, 'loner');

    $this
      ->createRelationship($manager, $master, $slaveRead, RelationshipCapability::Read, true)
      ->createRelationship($manager, $master, $slaveWrite, RelationshipCapability::Write, true)
      ->createRelationship($manager, $master, $slaveReadPending, RelationshipCapability::Read, false)
      ->createRelationship($manager, $master, $slaveWritePending, RelationshipCapability::Write, false)
      ->createRelationship($manager, $slaveRead, $subSlave, RelationshipCapability::Write, true)
    ;

    $this
      ->createWeightRecords($manager, $master, fn (DateTimeImmutable $date) => 100 + (int) $date->format('j'))
      ->createWeightRecords($manager, $slaveRead, fn (DateTimeImmutable $date) => 200 - (int) $date->format('j'))
      ->createWeightRecords($manager, $slaveWrite, fn (DateTimeImmutable $date) => 0 === (int) $date->format('j') % 2 ? 125 : 175)
      ->createWeightRecords($manager, $slaveReadPending, fn (DateTimeImmutable $date) => (int) $date->format('j') % 4 < 2 ? 175 : 200)
      ->createWeightRecords($manager, $slaveWritePending, fn (DateTimeImmutable $date) => (int) $date->format('N') < 6 ? 95 : 195)
      ->createWeightRecords($manager, $loner, fn () => 150)
    ;

    $manager->flush();
  }

  private function createUser(ObjectManager $manager, string $name): User
  {
    $user = (new User())->setUsername($name);
    $user->setPassword($this->userPasswordHasher->hashPassword($user, $name));
    $manager->persist($user);

    return $user;
  }

  private function createRelationship(ObjectManager $manager, User $from, User $to, RelationshipCapability $capability, bool $active): self
  {
    $relationship = (new Relationship())
      ->setFromUser($from)
      ->setToUser($to)
      ->setCapability($capability)
      ->setActive($active)
    ;
    $manager->persist($relationship);

    return $this;
  }

  /**
   * @param callable(DateTimeImmutable): int $weightGen
   */
  private function createWeightRecords(ObjectManager $manager, User $owner, callable $weightGen): self
  {
    /**
     * TODO https://github.com/vimeo/psalm/issues/9147.
     *
     * @psalm-suppress InvalidArgument
     */
    $dateIterator = new DatePeriod(
      new DateTimeImmutable('2022-01-01 00:00:00 UTC'),
      new DateInterval('P1D'),
      new DateTimeImmutable('2022-01-'.(string) self::WEIGHT_RECORDS_PER_USER.' 00:00:00 UTC'),
      DatePeriod::INCLUDE_END_DATE,
    );
    foreach ($dateIterator as $date) {
      $weightRecord = (new WeightRecord())
        ->setOwner($owner)
        ->setDate($date)
        ->setWeight($weightGen($date))
      ;
      $manager->persist($weightRecord);
    }

    return $this;
  }
}
