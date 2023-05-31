<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Entity\WeightRecord;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractCrudType<WeightRecord>
 */
class WeightRecordType extends AbstractCrudType
{
  public function __construct(
    Security $security,
  ) {
    parent::__construct($security);
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $this->addOwnerField($builder, $options);
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
    ]);
  }
}
