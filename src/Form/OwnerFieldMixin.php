<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;

use function assert;

trait OwnerFieldMixin
{
  public const OWNER_FIELD = 'owner';

  /**
   * @param array<string,mixed> $options
   */
  protected function addOwnerField(FormBuilderInterface $builder, Security $security, array $options = []): void
  {
    $user = $security->getUser();
    assert($user instanceof User);

    $options = array_merge($options, [
      'label' => 'auth.username',
      'class' => User::class,
      'choice_label' => 'username',
      'query_builder' => static fn (UserRepository $userRepository) => $userRepository->getControlledUsersIncludingSelfQuery( // phpcs:ignore Generic.Files.LineLength.TooLong
        [$user],
        RelationshipCapability::Write->permissionsRequired(),
      ),
    ]);
    $builder->add(self::OWNER_FIELD, EntityType::class, $options);
  }
}
