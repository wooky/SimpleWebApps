<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\Stub;
use SimpleWebApps\Auth\Ownable;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Auth\UserVoter;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\Repository\WeightRecordRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoterIntegrationTest extends KernelTestCase
{
  private UserVoter $voter;
  private UserRepository $userRepository;
  private WeightRecordRepository $weightRecordRepository;

  protected function setUp(): void
  {
    static::bootKernel();
    $container = static::getContainer();
    $this->voter = $container->get(UserVoter::class);
    $this->userRepository = $container->get(UserRepository::class);
    $this->weightRecordRepository = $container->get(WeightRecordRepository::class);
  }

  public function testUnsupported()
  {
    $token = $this->createToken(null);
    $ownable = $this->createStub(Ownable::class);
    static::assertSame(Voter::ACCESS_ABSTAIN, $this->voter->vote($token, 'not ownable', ['not capability']));
    static::assertSame(Voter::ACCESS_ABSTAIN, $this->voter->vote($token, $ownable, ['not capability']));
    static::assertSame(Voter::ACCESS_ABSTAIN, $this->voter->vote($token, 'not ownable', [RelationshipCapability::Read->value]));
  }

  public function testUserNotLoggedIn()
  {
    $token = $this->createToken(null);
    $ownable = $this->createStub(Ownable::class);
    static::assertSame(Voter::ACCESS_DENIED, $this->voter->vote($token, $ownable, [RelationshipCapability::Read->value]));
  }

  public function testOwnableBelongsToUser()
  {
    $user = $this->userRepository->findOneBy(['username' => 'master']);
    $token = $this->createToken($user);
    $ownable = $this->weightRecordRepository->findOneBy(['owner' => $user]);
    static::assertSame(Voter::ACCESS_GRANTED, $this->voter->vote($token, $ownable, [RelationshipCapability::Read->value]));
  }

  public function testOwnableCanBeAccessed()
  {
    $user = $this->userRepository->findOneBy(['username' => 'master']);
    $token = $this->createToken($user);
    $slave = $this->userRepository->findOneBy(['username' => 'slave-read']);
    $ownable = $this->weightRecordRepository->findOneBy(['owner' => $slave]);
    static::assertSame(Voter::ACCESS_GRANTED, $this->voter->vote($token, $ownable, [RelationshipCapability::Read->value]));
  }

  public function testOwnableInsufficientPermissions()
  {
    $user = $this->userRepository->findOneBy(['username' => 'master']);
    $token = $this->createToken($user);
    $slave = $this->userRepository->findOneBy(['username' => 'slave-read']);
    $ownable = $this->weightRecordRepository->findOneBy(['owner' => $slave]);
    static::assertSame(Voter::ACCESS_DENIED, $this->voter->vote($token, $ownable, [RelationshipCapability::Write->value]));
  }

  public function testOwnableNoRelationship()
  {
    $user = $this->userRepository->findOneBy(['username' => 'master']);
    $token = $this->createToken($user);
    $loner = $this->userRepository->findOneBy(['username' => 'loner']);
    $ownable = $this->weightRecordRepository->findOneBy(['owner' => $loner]);
    static::assertSame(Voter::ACCESS_DENIED, $this->voter->vote($token, $ownable, [RelationshipCapability::Read->value]));
  }

  private function createToken(?UserInterface $user): TokenInterface
  {
/** @var TokenInterface&Stub */ $token = $this->createStub(TokenInterface::class);
    $token->method('getUser')->willReturn($user);

    return $token;
  }
}
