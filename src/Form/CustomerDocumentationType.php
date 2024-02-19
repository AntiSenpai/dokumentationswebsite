<?php

namespace App\Form;

use App\Entity\CustomerDocumentation;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerDocumentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('sectionType', ChoiceType::class, [
            // Configure your options, like choice options
        ])
        ->add('content', TextareaType::class)
        ->add('createdAt', DateTimeType::class)
        ->add('updatedAt', DateTimeType::class)
        // Add other fields as necessary
        ->add('save', SubmitType::class, ['label' => 'Speichern']);
}


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomerDocumentation::class,
        ]);
    }
}
