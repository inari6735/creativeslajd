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
                'label' => 'form.name',
                'constraints' => [
                    new NotBlank(['message' => 'form.name_required']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'form.name_min',
                        'maxMessage' => 'form.name_max',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'form.name_placeholder'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'form.description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'form.description_placeholder'
                ]
            ])
            ->add('isPublic', CheckboxType::class, [
                'label' => 'form.is_public',
                'required' => false,
                'help' => 'form.is_public_help'
            ])
            ->add('isPubliclyEditable', CheckboxType::class, [
                'label' => 'form.is_public_editable',
                'required' => false,
                'help' => 'form.is_public_editable_help'
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
