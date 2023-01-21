<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use SimpleWebApps\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<User>
 */
class RegistrationFormType extends AbstractType
{
  /**
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
        ->add('username', options: [
            'label' => 'auth.username',
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('plainPassword', PasswordType::class, [
            'label' => 'auth.password',
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    // 'message' => 'Please enter a password',
                ]),
                new Length([
                    'min' => 6,
                    // 'minMessage' => 'Your password should be at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
        ->add('agreeTerms', CheckboxType::class, [
            'label' => 'auth.agree_terms',
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'auth.didnt_agree_terms',
                ]),
            ],
        ])
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
        'data_class' => User::class,
    ]);
  }
}
