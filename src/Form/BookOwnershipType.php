<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Book\BookOwnershipState;
use SimpleWebApps\Entity\BookOwnership;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookOwnershipType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('book', BookType::class)
      // ->add('owner')
      ->add('state', EnumType::class, [
        'class' => BookOwnershipState::class,
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
        'data_class' => BookOwnership::class,
    ]);
  }
}
