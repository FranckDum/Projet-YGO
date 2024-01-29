<?php

namespace App\Form;

use App\Entity\TProduits;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TProduitsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('activation')
            ->add('stock')
            ->add('prix')
            ->add('nom_produit')
            ->add('ygo_id')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TProduits::class,
        ]);
    }
}
