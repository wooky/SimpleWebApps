<?php declare(strict_types = 1);
namespace SimpleWebApps\Form;

use SimpleWebApps\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<User>
 */
class ProfileFormType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'form.ulid',
                'disabled' => true,
            ])
            ->add('username', TextType::class, [
                'label' => 'auth.username',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'auth.password',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.unchanged',
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
