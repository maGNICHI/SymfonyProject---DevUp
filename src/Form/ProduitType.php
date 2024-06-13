<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Entity\Produit;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'required' => false,
            ])
            ->add('description', null, [
                'required' => false,
            ])
            ->add('telContact')
            ->add('prix')
            ->add('categorie', EntityType::class, array(
                'class' => 'App\Entity\Categorie',
                'choice_label' => 'nom',
                'placeholder' => 'Choisir une catégorie '

            ))
            ->add('image', FileType::class, [
                'label' => 'Brochure (PDF file)',
                // non mappé signifie que ce champ n'est associé à aucune propriété d'entité
                'mapped' => false,
                // rendez-le facultatif afin que vous n'ayez pas à télécharger à nouveau le fichier PDF
                // chaque fois que vous modifiez les détails du produit
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
