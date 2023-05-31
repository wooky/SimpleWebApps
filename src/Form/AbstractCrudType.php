<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function assert;

/**
 * @template T
 *
 * @extends AbstractType<T>
 */
abstract class AbstractCrudType extends AbstractType
{
  public const IS_OWNER_DISABLED = 'is_owner_disabled';

  protected function __construct(
    private Security $security,
  ) {
    // Do nothing.
  }

  protected function addOwnerField(FormBuilderInterface $builder, array $options): void
  {
    assert(isset($options[self::IS_OWNER_DISABLED]));

    $user = $this->security->getUser();
    assert($user instanceof User);
    $builder
      ->add('owner', EntityType::class, [
        'label' => 'auth.username',
        'class' => User::class,
        'choice_label' => 'username',
        'query_builder' => fn (UserRepository $userRepository) => $userRepository->getControlledUsersIncludingSelfQuery($user, RelationshipCapability::Write->permissionsRequired()),
        'disabled' => $options[self::IS_OWNER_DISABLED],
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefault(self::IS_OWNER_DISABLED, true);
  }
}
