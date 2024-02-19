<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationDocumentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $builder
        ->add('location', EntityType::class, [
            'class' => Location::class,
            'choice_label' => 'name',
        ])
        // Add other fields if necessary
        ->add('save', SubmitType::class, ['label' => 'Speichern']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           'data_class' => Location::class,
        ]);
    }
}
