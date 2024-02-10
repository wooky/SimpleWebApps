<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Auth\AuthenticatedUser;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
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
    $authenticatedUser = $security->getUser();
    assert($authenticatedUser instanceof AuthenticatedUser);
    $owners = [
      $authenticatedUser->user,
      ...$authenticatedUser->iterateControlledUsers(RelationshipCapability::Write->permissionsRequired()),
    ];

    $options = array_merge($options, [
      'label' => 'auth.username',
      'class' => User::class,
      'choice_label' => 'username',
      'choices' => $owners,
    ]);
    $builder->add(self::OWNER_FIELD, EntityType::class, $options);
  }
}
