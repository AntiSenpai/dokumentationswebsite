<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\Location;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('name', TextType::class)
        ->add('adresse', TextType::class)
        ->add('beschreibung', TextareaType::class)
        ->add('istHauptstandort', CheckboxType::class, [
            'required' => false,
        ])
        ->add('unterstandort', EntityType::class, [
            'class' => Location::class,
            'choice_label' => 'name',
            'required' => false,
        ])
        ->add('customerId', EntityType::class, [
            'class' => Customer::class,
            'choice_label' => 'name',
        ])
        ->add('address', TextType::class)
        ->add('description', TextareaType::class)
        ->add('save', SubmitType::class, ['label' => 'Speichern']);
}


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
