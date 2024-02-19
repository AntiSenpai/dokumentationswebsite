<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('suchnummer', TextType::class)
            ->add('createdAt', DateTimeType::class, [
                // Custom options for the createdAt field
            ])
            ->add('updatedAt', DateTimeType::class, [
                // Custom options for the updatedAt field
                'required' => false,
            ])
            ->add('updatedBy', EntityType::class, [
                'class' => User::class,
                // Custom options for the updatedBy field
                'choice_label' => 'username',
            ])
            ->add('save', SubmitType::class, ['label' => 'Speichern'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
