<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un email svp',
                    ]),
                    new Email([
                        'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.',
                    ]),
                ],
                'required'=> false
            ])
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
                    new Regex([
                        'pattern' => "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{12,255}$/",
                        'message' => 'Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un chiffre, un caractère spécial et avoir une longueur minimale de 12 caractères.'
                    ]),
                ],
                'required'=> false
            ])
            ->add('nom', TextType::class, [
                'required'=> false,
                'constraints'=> [
                    new NotBlank([
                        'message' => 'Entrez un nom svp',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre nom doit contenir au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Votre nom doit contenir au maximum {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'required'=> false,
                'constraints'=> [
                    new NotBlank([
                        'message' => 'Entrez un nom svp',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre prénom doit contenir au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Votre prénom doit contenir au maximum {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('date_naissance', DateType::class, [
                'years' => range(date('Y'),date('Y') - 120),
                'required'=> false
            ])
            ->add('ville', TextType::class, [
                'required'=> false,
                'constraints'=> [
                    new NotBlank([
                        'message' => 'Entrez un nom de ville svp',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre un nom de ville doit contenir au moins {{ limit }} caractères.',
                        'max' => 100,
                        'maxMessage' => 'Votre un nom de ville doit contenir au maximum {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('code_postal', NumberType::class, [
                'required'=> false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez un code postal svp',
                    ]),
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
            ->add('adresse', TextType::class, [
                'required'=> false,
                'constraints'=> [
                    new NotBlank([
                        'message' => 'Entrez une adresse svp',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre adresse doit contenir au moins {{ limit }} caractères.',
                        'max' => 100,
                        'maxMessage' => 'Votre adresse doit contenir au maximum {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('tel', TextType::class, [
                'required'=> false,
                'constraints'=> [
                    new NotBlank([
                        'message' => 'Entrez un numéro de téléphone svp',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Votre adresse doit contenir au moins {{ limit }} chiffres.',
                        'max' => 14,
                        'maxMessage' => 'Votre numéro de téléphone est invalide.'
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
