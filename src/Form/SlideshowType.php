<?php

namespace App\Form;

use App\Entity\Slideshow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SlideshowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa pokazu',
                'constraints' => [
                    new NotBlank(['message' => 'Podaj nazwę pokazu']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Nazwa musi mieć minimum {{ limit }} znaki',
                        'maxMessage' => 'Nazwa może mieć maksymalnie {{ limit }} znaków',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'np. Prezentacja produktów'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Opcjonalny opis pokazu slajdów'
                ]
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'Udostępnij publicznie',
                'required' => false,
                'help' => 'Gdy zaznaczone, pokaz będzie dostępny dla wszystkich przez link'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Slideshow::class,
        ]);
    }
}
