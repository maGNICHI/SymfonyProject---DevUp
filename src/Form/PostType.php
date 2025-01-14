<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('image',FileType::class, [
                'label' => 'Brochure (PDF file)',

                // non mappé signifie que ce champ n'est associé à aucune propriété d'entité
                'mapped' => false,

                // rendez-le facultatif afin que vous n'ayez pas à télécharger à nouveau le fichier PDF
                // chaque fois que vous modifiez les détails du produit
                'required' => false,

                ])
            
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
