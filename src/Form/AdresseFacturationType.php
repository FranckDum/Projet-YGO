<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\AdresseFacturation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class AdresseFacturationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('adresse_facturation')
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
                        'message' => 'Entrez un prénom svp',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre prénom doit contenir au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Votre prénom doit contenir au maximum {{ limit }} caractères.'
                    ]),
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
            ->add('complement_adresse', TextType::class, [
                'required'=> false,
                'constraints'=> [
                    // new NotBlank([
                    //     'message' => 'Entrez une adresse svp',
                    // ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre adresse doit contenir au moins {{ limit }} caractères.',
                        'max' => 100,
                        'maxMessage' => 'Votre adresse doit contenir au maximum {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('cp', NumberType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdresseFacturation::class,
        ]);
    }
}
