<?php
namespace App\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class, [
            'required'=> false,
            'label' => 'Nom',
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
            ->add('email',EmailType::class)
            ->add('message', TextareaType::class, [
                'attr' => ['rows' => 6],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez un message svp',
                    ]),
                ],
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}