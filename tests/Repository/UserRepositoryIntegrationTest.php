<?php

declare(strict_types=1);

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryIntegrationTest extends KernelTestCase
{
  private UserRepository $userRepository;

  protected function setUp(): void
  {
    self::bootKernel();
    $container = self::getContainer();
    $this->userRepository = $container->get(UserRepository::class);
  }

  public function testGetControlledUsersIncludingSelf()
  {
    $user = $this->userRepository->findOneBy(['username' => 'master']);
    $controlledUsers = $this->usersToUsernames($this->userRepository->getControlledUsersIncludingSelf($user, RelationshipCapability::Write->permissionsRequired()));
    static::assertEqualsCanonicalizing(['master', 'slave-write'], $controlledUsers);
  }

  public function testGetControllingUsersIncludingSelf()
  {
    $user = $this->userRepository->findOneBy(['username' => 'sub-slave']);
    $controllingUsers = $this->usersToUsernames($this->userRepository->getControllingUsersIncludingSelf($user, RelationshipCapability::Read->permissionsRequired()));
    static::assertEqualsCanonicalizing(['sub-slave', 'slave-read'], $controllingUsers);
  }

  /**
   * @param User[] $users
   *
   * @return string[]
   */
  private function usersToUsernames(array $users): array
  {
    return array_map(fn (User $user) => $user->getUsername(), $users);
  }
}
