<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Entity\Book;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

use function assert;

/**
 * @extends AbstractType<Book>
 */
class BookType extends AbstractType
{
  use OwnerFieldMixin;

  public const ADD_OWNER_FIELD = 'add_owner_field';
  public const IS_PUBLIC_DISABLED = 'is_public_disabled';

  public function __construct(
    private Security $security,
  ) {
    // Do nothing.
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    assert(isset($options[self::ADD_OWNER_FIELD]));
    assert(isset($options[self::IS_PUBLIC_DISABLED]));

    if ($options[self::ADD_OWNER_FIELD]) {
      $this->addOwnerField($builder, $this->security, [
        'mapped' => false,
        'data' => $this->security->getUser(),
      ]);
    }
    $builder
        ->add('title')
        ->add('description')
        ->add('isPublic', options: [
          'disabled' => $options[self::IS_PUBLIC_DISABLED],
          'help' => new TranslatableMessage('book_library.public_warning'),
        ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Book::class,
      self::ADD_OWNER_FIELD => true,
      self::IS_PUBLIC_DISABLED => false,
    ]);
  }
}
