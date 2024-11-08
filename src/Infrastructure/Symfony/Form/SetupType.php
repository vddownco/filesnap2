<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Form;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\PasswordStrength;

final class SetupType extends AbstractType
{
    public function __construct(#[Autowire(param: 'app.environment')] private readonly string $env)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $adminPlainPasswordConstraints = [];

        if ($this->env === 'prod') {
            $adminPlainPasswordConstraints[] = new PasswordStrength([
                'minScore' => PasswordStrength::STRENGTH_STRONG,
                'message' => 'Your password is too weak, please type a stronger password.',
            ]);
        }

        $builder
            ->add('adminEmail', EmailType::class, [
                'required' => true,
            ])
            ->add('adminPlainPassword', PasswordType::class, [
                'required' => true,
                'constraints' => $adminPlainPasswordConstraints,
            ])
            ->add('dbAlreadyCreated', CheckboxType::class, [
                'required' => false,
            ]);
    }
}
