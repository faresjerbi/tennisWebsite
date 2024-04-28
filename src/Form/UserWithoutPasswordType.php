<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UserWithoutPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom',
                'required' => false,
                'attr' => ['class' => 'form-control', 'style' => 'width: 100%'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est nécessaire']),
                    new Length(['max' => 10, 'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères']),
                ],
            ])
            ->add('prenom', null, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => ['class' => 'form-control', 'style' => 'width: 100%'],
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est nécessaire']),
                    new Length(['max' => 10, 'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères']),
                ],
            ])
            ->add('email', null, [
                'label' => 'Mail',
                'required' => false,
                'attr' => ['class' => 'form-control', 'style' => 'width: 100%'],
                'constraints' => [
                    new NotBlank(['message' => 'L\'adresse email est nécessaire']),
                    new Email(['message' => 'L\'adresse email n\'est pas valide']),
                ],
            ])
            ->add('genre', null, [
                'label' => 'Genre',
                'required' => false,
                'attr' => ['style' => 'width: 100px; margin-right: 20px; display: inline-block;'],
                'constraints' => [
                    new NotBlank(['message' => 'Le genre est nécessaire']),
                ],
            ])
            ->add('date_de_naissance', DateType::class, [
                'widget' => 'single_text',
                'placeholder' => [
                    'year' => 'YYYY',
                    'month' => 'MM',
                    'day' => 'DD',
                ],
                'empty_data' => function ($form) {
                    $entity = $form->getData();
                    if ($entity && $entity->getDateDeNaissance() !== null) {
                        return $entity->getDateDeNaissance()->format('Y-m-d'); // Return a string representation of the date
                    } else {
                        return date('Y-m-d'); // Default to current date
                    }
                },
                'constraints' => [
                    new NotBlank(['message' => 'La date de naissance est nécessaire']),
                ],
            ])
            ->add('niveau', null, [
                'label' => 'Niveau',
                'required' => false,
                'attr' => ['style' => 'width: 150px; margin-right: 20px; display: inline-block;'],
            ])
            ->add('disponibilite', null, [
                'label' => 'Disponibilité',
                'required' => false,
                'attr' => ['style' => 'width: 150px; margin-bottom: 20px; display: inline-block;'],
            ])
            ->add('img', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
