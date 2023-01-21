<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use function assert;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<void>
 */
class LoginFormType extends AbstractType
{
  public const LAST_USERNAME = 'last_username';

  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    assert(isset($options[self::LAST_USERNAME]));
    $builder
      ->add('_username', TextType::class, [
        'label' => 'auth.username',
        'data' => $options[self::LAST_USERNAME],
      ])
      ->add('_password', PasswordType::class, [
        'label' => 'auth.password',
      ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      self::LAST_USERNAME => '',
      'csrf_protection' => true,
      'csrf_field_name' => '_csrf_token',
      'csrf_token_id' => 'authenticate',
    ]);
  }

  public function getBlockPrefix(): string
  {
    return '';
  }
}
