<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Votre nom complet',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Veuillez entrer votre nom'
                    ),
                    new Assert\Length(
                        min: 2,
                        max: 100,
                        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'votre@email.com',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Veuillez entrer votre email'
                    ),
                    new Assert\Email(
                        message: 'L\'email {{ value }} n\'est pas valide'
                    ),
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => [
                    'placeholder' => 'Objet de votre message',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Veuillez entrer un sujet'
                    ),
                    new Assert\Length(
                        min: 3,
                        max: 200,
                        minMessage: 'Le sujet doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'Le sujet ne peut pas dépasser {{ limit }} caractères'
                    ),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'placeholder' => 'Votre message...',
                    'rows' => 8,
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Veuillez entrer votre message'
                    ),
                    new Assert\Length(
                        min: 10,
                        max: 5000,
                        minMessage: 'Le message doit contenir au moins {{ limit }} caractères',
                        maxMessage: 'Le message ne peut pas dépasser {{ limit }} caractères'
                    ),
                ],
            ])
            ->add('rgpdConsent', CheckboxType::class, [
                'label' => 'J\'accepte que mes données personnelles soient utilisées pour répondre à ma demande',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(
                        message: 'Vous devez accepter la politique de confidentialité pour envoyer le message'
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // No data class, we're just collecting form data
        ]);
    }
}
