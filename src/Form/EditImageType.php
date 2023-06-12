<?php

declare(strict_types=1);

namespace SimpleWebApps\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\Dropzone\Form\DropzoneType;

/**
 * @extends AbstractType<void>
 */
class EditImageType extends AbstractType
{
  /**
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('dropzone', DropzoneType::class)
    ;
  }
}
