<?php

declare(strict_types=1);

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Repository\RelationshipRepository;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RelationshipRepositoryIntegrationTest extends KernelTestCase
{
  private RelationshipRepository $relationshipRepository;
  private UserRepository $userRepository;

  protected function setUp(): void
  {
    static::bootKernel();
    $container = static::getContainer();
    $this->relationshipRepository = $container->get(RelationshipRepository::class);
    $this->userRepository = $container->get(UserRepository::class);
  }

  public function testFindBidirectionalRelationships()
  {
    $user = $this->userRepository->findOneBy(['username' => 'slave-read']);
    $relationships = array_map(
      fn (Relationship $relationship) => $this->relationshipShape($relationship),
      $this->relationshipRepository->findBidirectionalRelationships($user)
    );
    static::assertEqualsCanonicalizing([
      ['from' => 'master', 'to' => 'slave-read', 'capability' => RelationshipCapability::Read, 'active' => true],
      ['from' => 'slave-read', 'to' => 'sub-slave', 'capability' => RelationshipCapability::Write, 'active' => true],
    ], $relationships);
  }

  public function testFindActiveRelationshipExists()
  {
    $from = $this->userRepository->findOneBy(['username' => 'master']);
    $to = $this->userRepository->findOneBy(['username' => 'slave-write']);
    $relationship = $this->relationshipRepository->findActiveRelationship($from, $to, RelationshipCapability::Read->permissionsRequired());
    static::assertNotNull($relationship);
  }

  public function testFindActiveRelationshipNoCapability()
  {
    $from = $this->userRepository->findOneBy(['username' => 'master']);
    $to = $this->userRepository->findOneBy(['username' => 'slave-read']);
    $relationship = $this->relationshipRepository->findActiveRelationship($from, $to, RelationshipCapability::Write->permissionsRequired());
    static::assertNull($relationship);
  }

  public function testFindActiveRelationshipInactive()
  {
    $from = $this->userRepository->findOneBy(['username' => 'master']);
    $to = $this->userRepository->findOneBy(['username' => 'slave-write-pending']);
    $relationship = $this->relationshipRepository->findActiveRelationship($from, $to, RelationshipCapability::Read->permissionsRequired());
    static::assertNull($relationship);
  }

  public function testFindActiveRelationshipNonExisting()
  {
    $from = $this->userRepository->findOneBy(['username' => 'master']);
    $to = $this->userRepository->findOneBy(['username' => 'loner']);
    $relationship = $this->relationshipRepository->findActiveRelationship($from, $to, RelationshipCapability::Read->permissionsRequired());
    static::assertNull($relationship);
  }

  private function relationshipShape(Relationship $relationship): array
  {
    return [
      'from' => $relationship->getFromUser()->getUsername(),
      'to' => $relationship->getToUser()->getUsername(),
      'capability' => $relationship->getCapability(),
      'active' => $relationship->isActive(),
    ];
  }
}
