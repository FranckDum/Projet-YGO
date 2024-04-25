<?php

namespace App\Form;

use App\Entity\Commandes;
use App\Entity\DetailCommande;
use App\Entity\TProduits;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prix')
            ->add('quantity')
            ->add('commandes', EntityType::class, [
                'class' => Commandes::class,
'choice_label' => 'id',
            ])
            ->add('tProduits', EntityType::class, [
                'class' => TProduits::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetailCommande::class,
        ]);
    }
}
