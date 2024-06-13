<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Rate;

class RateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etoile',ChoiceType::class,[
               'choices'=>[
                '1 etoile'=>1,
                '2 etoiles'=>2,
                '3 etoile'=>3,
                '4 etoile'=>4,
                '5 etoile'=>5,
               ],
               'expanded'=>true,
               'multiple'=>false,


            ])
            ->add('commentaire',TextareaType::class,[
                'attr'=>[
                    'rows'=>'2',
                    'cols'=>'50',
                    'placeholder'=>'Saisissez votre commentaire',

                ],
                
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
         'data_class'=>Rate::class,

        ]);
    }
}
