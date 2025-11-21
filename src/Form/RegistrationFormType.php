<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'auth.email',
                'attr' => [
                    'placeholder' => 'auth.email_placeholder',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'auth.password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'auth.password_min',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'auth.password_required',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'auth.password_min_message',
                            'max' => 4096,
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'auth.password_repeat',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'auth.password_repeat',
                    ],
                ],
                'invalid_message' => 'auth.password_mismatch',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'auth.agree_terms',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'auth.agree_terms_required',
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
