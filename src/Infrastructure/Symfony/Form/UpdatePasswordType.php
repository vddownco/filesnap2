<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<FormBuilder>
 */
final class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'required' => true,
            ])
            ->add('newPassword', PasswordType::class, [
                'required' => true,
            ]);
    }
}
