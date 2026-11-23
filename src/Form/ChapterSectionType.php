<?php

namespace App\Form;

use App\Entity\ChapterSection;
use App\EntityEnum\ChapterSectionLanguage;
use App\EntityEnum\ChapterSectionType as SectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapterSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', null, [
                'label' => false,
                'row_attr' => [
                    'class' => 'form-field',
                ],
                'attr' => [
                    'class' => 'form-ghost p-5',
                    'placeholder' => 'Contenu',
                    'aria-label' => 'Contenu',
                ],
            ])
            // Set by the "Ajouter" menu of the ChapterCollectionType component.
            ->add('type', HiddenType::class)
            ->add('variant')
            // Only rendered for code sections; null means plain text.
            ->add('language', EnumType::class, [
                'class' => ChapterSectionLanguage::class,
                'choice_label' => fn (ChapterSectionLanguage $language): string => $language->label(),
                'label' => 'Langage',
                'required' => false,
                'placeholder' => 'Texte brut',
            ])
            // shortcut: set to the row index on add, until positions are managed automatically.
            ->add('position', HiddenType::class)
        ;

        $builder->get('type')->addModelTransformer(new CallbackTransformer(
            fn (?SectionType $type): ?string => $type?->value,
            fn (?string $value): ?SectionType => null === $value || '' === $value
                ? null
                : SectionType::tryFrom($value) ?? throw new TransformationFailedException(\sprintf('Invalid section type "%s".', $value)),
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChapterSection::class,
            // Without a class, the form theme adds mb-3 to the section's fieldset.
            'row_attr' => [
                'class' => 'form-group',
            ],
        ]);
    }
}
