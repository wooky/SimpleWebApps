<?php declare(strict_types = 1);
namespace SimpleWebApps\Form;

use SimpleWebApps\Auth\RelationshipCapability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\UlidType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<void>
 */
class InviteFormType extends AbstractType
{
    const TO_USER = 'toUser';
    const CAPABILITY = 'capability';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::TO_USER, UlidType::class, [
                'label' => "User ULID",
            ])
            ->add(self::CAPABILITY, EnumType::class, [
                'class' => RelationshipCapability::class,
            ]);
        ;
    }
}
