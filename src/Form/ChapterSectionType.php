<?php

namespace App\Form;

use App\Entity\Callout;
use App\Entity\ChapterSection;
use App\Entity\Code;
use App\Entity\Title;
use App\EntityEnum\ChapterSectionLanguage;
use App\EntityEnum\ChapterSectionType as SectionType;
use App\EntityEnum\ChapterSectionVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Fields for every section type; the ChapterCollectionType template only renders those of the
 * section's own type, and the others read null and write nothing.
 *
 * @extends AbstractType<ChapterSection>
 */
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
                    'class' => 'form-ghost',
                    'placeholder' => 'Contenu',
                    'aria-label' => 'Contenu',
                ],
            ])
            // Set by the "Ajouter" menu of the ChapterCollectionType component; picks the class of a new section (see empty_data).
            ->add('type', HiddenType::class, [
                'getter' => fn (ChapterSection $section): string => $section->getType()->value,
                // The class is the type: an existing section never changes it.
                'setter' => fn (): null => null,
            ])
            ->add('variant', EnumType::class, [
                'class' => ChapterSectionVariant::class,
                'required' => false,
                'getter' => fn (ChapterSection $section): ?ChapterSectionVariant => $section instanceof Callout ? $section->getVariant() : null,
                'setter' => fn (ChapterSection $section, ?ChapterSectionVariant $variant): ?Callout => $section instanceof Callout ? $section->setVariant($variant) : null,
            ])
            ->add('level', ChoiceType::class, [
                'choices' => array_combine(array_map(fn (int $level): string => 'Niveau '.$level, range(2, 6)), range(2, 6)),
                'label' => 'Niveau',
                'required' => false,
                'getter' => fn (ChapterSection $section): ?int => $section instanceof Title ? $section->getLevel() : null,
                'setter' => fn (ChapterSection $section, ?int $level): ?Title => $section instanceof Title ? $section->setLevel($level) : null,
            ])
            // Null means plain text.
            ->add('language', EnumType::class, [
                'class' => ChapterSectionLanguage::class,
                'choice_label' => fn (ChapterSectionLanguage $language): string => $language->label(),
                'label' => 'Langage',
                'required' => false,
                'placeholder' => 'Texte brut',
                'getter' => fn (ChapterSection $section): ?ChapterSectionLanguage => $section instanceof Code ? $section->getLanguage() : null,
                'setter' => fn (ChapterSection $section, ?ChapterSectionLanguage $language): ?Code => $section instanceof Code ? $section->setLanguage($language) : null,
            ])
            // Set by the component on add/insert and by drag and drop (sortable_controller.ts).
            ->add('position', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChapterSection::class,
            // ChapterSection is abstract: a new section is built from its submitted type (children are submitted first).
            'empty_data' => fn (FormInterface $form): ChapterSection => SectionType::tryFrom((string) $form->get('type')->getData())?->newSection()
                ?? throw new BadRequestHttpException('Invalid section type.'),
            // Without a class, the form theme adds mb-3 to the section's fieldset.
            'row_attr' => [
                'class' => 'form-group',
            ],
        ]);
    }
}
