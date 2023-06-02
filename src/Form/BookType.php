<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Entity\Book;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Book>
 */
class BookType extends AbstractType
{
  use OwnerFieldMixin;

  public function __construct(
    private Security $security,
  ) {
    // Do nothing.
  }

  /**
   * TODO https://github.com/phpmd/phpmd/issues/515.
   *
   * @SuppressWarnings(PHPMD.UnusedFormalParameters)
   */
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $this->addOwnerField($builder, $this->security, [
      'mapped' => false,
      'data' => $this->security->getUser(),
    ]);
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
