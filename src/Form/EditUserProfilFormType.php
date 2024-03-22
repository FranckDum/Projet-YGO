<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class EditUserProfilFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $user = $options['user'];

        
        if ( in_array("ROLE_ADMIN", $user->getRoles()) ) 
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
                ->add('roles', ChoiceType::class, [
                    // 'mapped' => true, // Ne pas mapper ce champ à l'entité User
                    // 'required' => false, // Champ facultatif car les rôles peuvent être vides
                    'choices' => [
                        "Utilisateur" => "ROLE_USER",
                        "Administrateur" => "ROLE_ADMIN"
                    ],
                    'expanded' => false,
                    'multiple' => true
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
                        ]);
        }

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
                ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            "user" => []
        ]);
    }
}
