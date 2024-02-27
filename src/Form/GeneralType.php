<?php

namespace App\Form;

use App\Entity\GeneralInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GeneralType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
        ->add("id", TextType::class, ['label' => 'iD'])#
        ->add('Standort', TextType::class, ['label'=> 'Standort'])
        ->add('Ansprechpartner', TextType::class, ['label'=> 'Ansprechpartner'])
        ->add('Telefon', TextType::class, ['label'=> 'Telefon'])
        ->add('Mobil', TextType::class, ['label'=> 'Mobil'])
        ->add('Email', EmailType::class, ['label'=> 'E-Mail'])

        ;

    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class'=> GeneralInfo::class,
        ]);
    }

}