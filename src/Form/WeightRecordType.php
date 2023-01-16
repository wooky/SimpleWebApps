<?php declare(strict_types = 1);
namespace SimpleWebApps\Form;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<WeightRecord>
 */
class WeightRecordType extends AbstractType
{
    const IS_OWNER_DISABLED = 'is_owner_disabled';

    public function __construct(
        private Security $security,
    ) {
        // Do nothing.
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User */ $user = $this->security->getUser();
        $builder
            ->add('owner', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'query_builder' => fn(UserRepository $userRepository) =>
                    $userRepository->getControlledUsersIncludingSelfQuery($user, RelationshipCapability::Write->permissionsRequired())
                    ,
                'disabled' => $options[self::IS_OWNER_DISABLED],
            ])
            ->add('date', options: [
                'widget' => 'single_text',
            ])
            ->add('weight')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WeightRecord::class,
            self::IS_OWNER_DISABLED => true,
        ]);
    }
}
