<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le nouveau mot de passe est obligatoire.',
                        ]),
                        new Length([
                            'min' => 12,
                            'max' =>255,
                            'minMessage' => 'Le nouveau mot de passe doit contenir au minimum {{limit}} caractères.',
                            'maxMessage' => 'Le nouveau mot de passe doit contenir au maximum {{limit}} caractères.' 
                        ]),
                        new Regex([
                            'pattern' => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{12,255}$/",
                            'message' => 'Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un chiffre, un caractère spécial et avoir une longueur minimale de 12 caractères.'
                        ]),
                        new NotCompromisedPassword([
                            'message' => "Votre mot de passe est facilement piratable! Veuillez en choisir un autre."
                        ])
                    ],
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'label' => 'Répétez le nouveau mot de passe',
                ],
                'invalid_message' => 'Le nouveau mot de passe doit être identique à sa confirmation.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
