<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Book>
 */
class BookType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
        ->add('title')
        ->add('description')
        ->add('isPublic')
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
        'data_class' => Book::class,
    ]);
  }
}
