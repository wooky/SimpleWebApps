<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Entity\WeightRecord;
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
  use OwnerFieldMixin;

  public const IS_OWNER_DISABLED = 'is_owner_disabled';

  public function __construct(
    private readonly Security $security,
  ) {
    // Do nothing.
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    assert(isset($options[self::IS_OWNER_DISABLED]));
    $this->addOwnerField($builder, $this->security, ['disabled' => $options[self::IS_OWNER_DISABLED]]);
    $builder
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
    parent::configureOptions($resolver);
    $resolver->setDefaults([
      'data_class' => WeightRecord::class,
      self::IS_OWNER_DISABLED => true,
    ]);
  }
}
