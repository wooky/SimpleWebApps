<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Book\BookOwnershipState;
use SimpleWebApps\Entity\BookOwnership;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractCrudType<BookOwnership>
 */
class BookOwnershipType extends AbstractCrudType
{
  public function __construct(
    Security $security,
  ) {
    parent::__construct($security);
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('book', BookType::class)
    ;
    $this->addOwnerField($builder, $options);
    $builder
      ->add('state', EnumType::class, [
        'class' => BookOwnershipState::class,
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    parent::configureOptions($resolver);
    $resolver->setDefaults([
        'data_class' => BookOwnership::class,
    ]);
  }
}
