<?php

namespace App\Form;

use App\Entity\Chapter;
use App\Entity\Course;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Titre'
                ],
            ])
            ->add('excerpt', TextareaType::class, [
                'label' => 'Résumé',
                'attr' => [
                    'placeholder' => 'Résumé',
                ],
            ])
            ->add('position', null, [
                'label' => 'Position',
                'attr' => [
                    'placeholder' => 'Position',
                ],
            ])
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
        ]);
    }
}
