<?php

namespace App\Form;

use App\Entity\Chapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

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
            ->add('sections', LiveCollectionType::class, [
                'entry_type' => ChapterSectionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'button_delete_options' => [
                    'label' => 'Supprimer',
                    'attr' => [
                        'class' => 'btn-subtle theme-danger',
                    ],
                ],
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
