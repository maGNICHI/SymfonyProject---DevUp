<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'required' => false,
            ]);
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {//validy donne soumises par le formulaireet enregistre dans bd  on utilise le setters de classe categorie
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
