<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterCardsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // dd($options['filterTypes']);
        $builder
            ->add('type', ChoiceType::class, [
                'choices'  => $options['filterTypes'],
                'expanded' => false,
                'multiple' => false,
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    return $value;
                },
                'placeholder' => "le type",
                "required" => false
            ])
            ->add('atk', ChoiceType::class, [
                'choices'  => $options['filterAtks'],
                'expanded' => false,
                'multiple' => false,
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    return $value;
                },
                'placeholder' => "l'attaque",
                "required" => false
            ])
            ->add('def', ChoiceType::class, [
                'choices'  => $options['filterDefs'],
                'expanded' => false,
                'multiple' => false,
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    return $value;
                },
                'placeholder' => "la défense",
                "required" => false
            ])
            ->add('level', ChoiceType::class, [
                'choices'  => $options['filterLevels'],
                'expanded' => false,
                'multiple' => false,
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    return $value;
                },
                'placeholder' => "le niveau",
                "required" => false
            ])
            ->add('race', ChoiceType::class, [
                'choices'  => $options['filterRaces'],
                'expanded' => false,
                'multiple' => false,
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    return $value;
                },
                'placeholder' => "la race",
                "required" => false
            ])
            ->add('attribute', ChoiceType::class, [
                'choices'  => $options['filterAttributes'],
                'expanded' => false,
                'multiple' => false,
                'choice_label' => function ($choice, string $key, mixed $value): string {
                    return $value;
                },
                'placeholder' => "l'attribut",
                "required" => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            "filterTypes"       => [],
            "filterAtks"        => [],
            "filterDefs"        => [],
            "filterLevels"      => [],
            "filterRaces"       => [],
            "filterAttributes"  => [],
            "method" => "GET",
            "csrf_protection" => false
        ]);
    }
}
