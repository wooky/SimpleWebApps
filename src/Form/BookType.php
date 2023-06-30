<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Book\BookPublicity;
use SimpleWebApps\Entity\Book;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function assert;

/**
 * @extends AbstractType<Book>
 */
class BookType extends AbstractType
{
  use OwnerFieldMixin;

  public const ADD_OWNER_FIELD = 'add_owner_field';
  public const PUBLICITY_VALUES = 'publicity_values';

  public function __construct(
    private readonly Security $security,
  ) {
    // Do nothing.
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    assert(isset($options[self::ADD_OWNER_FIELD]));
    assert(isset($options[self::PUBLICITY_VALUES]));

    if ($options[self::ADD_OWNER_FIELD]) {
      $this->addOwnerField($builder, $this->security, [
        'mapped' => false,
        'data' => $this->security->getUser(),
      ]);
    }
    $builder
        ->add('title', options: [
          'label' => 'book_library.title',
        ])
        ->add('description', options: [
          'label' => 'book_library.description',
        ])
        ->add('publicity', EnumType::class, options: [
          'class' => BookPublicity::class,
          'label' => 'book_library.is_public',
          'help' => 'book_library.public_warning',
          'choices' => $options[self::PUBLICITY_VALUES],
        ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Book::class,
      self::ADD_OWNER_FIELD => true,
      self::PUBLICITY_VALUES => [],
    ]);
  }
}
