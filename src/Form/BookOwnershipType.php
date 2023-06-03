<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Book\BookOwnershipState;
use SimpleWebApps\Entity\BookOwnership;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function assert;

/**
 * @extends AbstractType<BookOwnership>
 */
class BookOwnershipType extends AbstractType
{
  use OwnerFieldMixin;

  public const IS_OWNER_DISABLED = 'is_owner_disabled';

  public function __construct(
    private Security $security,
  ) {
    // Do nothing.
  }

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    assert(isset($options[self::IS_OWNER_DISABLED]));
    $builder
      ->add('book', TextType::class, [
        'disabled' => true,
        'getter' => fn (BookOwnership $bookOwnership) => $bookOwnership->getBook()?->getTitle(),
      ])
    ;
    $this->addOwnerField($builder, $this->security, ['disabled' => $options[self::IS_OWNER_DISABLED]]);
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
      self::IS_OWNER_DISABLED => true,
    ]);
  }
}
