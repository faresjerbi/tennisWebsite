<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => ['style' => 'width: 200px; margin-right: 20px; display: inline-block;']
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => ['style' => 'width: 200px; margin-right: 20px; display: inline-block;']
            ])
            ->add('mail', TextType::class, [
                'label' => 'Mail',
                'required' => false,
                'attr' => ['style' => 'width: 300px; margin-right: 20px; display: inline-block;']
            ])
            ->add('password', TextType::class, [
                'label' => 'Mot de passe',
                'required' => false,
                'attr' => ['style' => 'width: 200px; margin-right: 20px; display: inline-block;']
            ])
            ->add('genre', TextType::class, [
                'label' => 'Genre',
                'required' => false,
                'attr' => ['style' => 'width: 100px; margin-right: 20px; display: inline-block;']
            ])
            ->add('date_de_naissance', DateType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'attr' => ['style' => 'width: 200px; margin-right: 20px; display: inline-block;']
            ])
            ->add('role', TextType::class, [
                'label' => 'Rôle',
                'required' => false,
                'attr' => ['style' => 'width: 150px; margin-right: 20px; display: inline-block;']
            ])
            ->add('niveau', TextType::class, [
                'label' => 'Niveau',
                'required' => false,
                'attr' => ['style' => 'width: 150px; margin-right: 20px; display: inline-block;']
            ])
            ->add('disponibilite', TextType::class, [
                'label' => 'Disponibilité',
                'required' => false,
                'attr' => ['style' => 'width: 150px; margin-bottom: 20px; display: inline-block;']
            ]);
           // Ajoutez d'autres champs si nécessaire
      /*    ->add('img', TextType::class, [
            'label' => 'Image',
            'required' => false, // Vous pouvez modifier cette option selon vos besoins
        ]);*/
    }
         
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
