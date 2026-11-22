<?php

namespace App\Form;

use App\Entity\ChapterSection;
use App\EntityEnum\ChapterSectionType as SectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapterSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content')
            // Set by the "Ajouter" menu of the ChapterCollectionType component.
            ->add('type', HiddenType::class)
            ->add('variant')
            ->add('position')
            ->add('parent', EntityType::class, [
                'class' => ChapterSection::class,
                'choice_label' => 'id',
                'required' => false,
            ])
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
        ]);
    }
}
