<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les conditions d'utilisations",
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                
                'type' => PasswordType::class,
                                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répétez votre mot de passe'],
                'invalid_message' => 'Votre mot de passe est invalide',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez un mot de passe svp',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('nom')
            ->add('prenom')
            ->add('date_naissance', DateType::class, [
                'years' => range(date('Y') - 120, date('Y')),
            ])
            ->add('ville')
            ->add('code_postal', NumberType::class, [
                'constraints' => [
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Le code postal doit contenir 5 chiffres',
                        'max' =>5,
                        
                        'maxMessage' => 'Le code postal ne doit pas contenir plus de 5 chiffres'
                    ]),
                    new Positive([
                        'message' => 'Veuillez saisir un nombre positif'
                    ])
                ],
            ])
            ->add('adresse')
            ->add('tel')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
