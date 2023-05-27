<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function assert;

/**
 * @extends AbstractType<WeightRecord>
 */
class WeightRecordType extends AbstractType
{
  public const IS_OWNER_DISABLED = 'is_owner_disabled';

  public function __construct(
    private Security $security,
  ) {
    // Do nothing.
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
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
        ->add('date', options: [
            'label' => 'weight_tracker.date',
            'widget' => 'single_text',
        ])
        ->add('weight', options: [
            'label' => 'weight_tracker.weight',
        ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
        'data_class' => WeightRecord::class,
        self::IS_OWNER_DISABLED => true,
    ]);
  }
}
