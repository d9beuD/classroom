<?php

namespace App\Twig\Components;

use App\Entity\Chapter;
use App\EntityEnum\ChapterSectionType;
use App\Form\ChapterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent]
final class ChapterCollectionType extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    #[LiveProp]
    public ?Chapter $initialFormData = null;

    // Live re-renders don't see the parent template's variables, so they come in as props.
    #[LiveProp]
    public string $buttonLabel = 'Créer';

    #[LiveProp]
    public string $cancelUrl;

    /** @return list<ChapterSectionType> */
    public function getSectionTypes(): array
    {
        return ChapterSectionType::cases();
    }

    #[LiveAction]
    /**
     * Adds a section of the given type at the end, or right after the section with collection
     * key $after (later sections move down one position).
     */
    public function addSection(PropertyAccessorInterface $propertyAccessor, #[LiveArg] string $type, #[LiveArg] ?string $after = null): void
    {
        $type = ChapterSectionType::tryFrom($type) ?? throw new BadRequestHttpException('Invalid section type.');

        $sections = $this->formValues['sections'] ?? [];
        $positions = array_map(fn (array $section): int => (int) ($section['position'] ?? 0), $sections);
        $position = null;
        if (null !== $after) {
            $position = ($positions[$after] ?? throw new BadRequestHttpException('Unknown section.')) + 1;
        }
        if (null === $position) {
            $position = [] === $positions ? 0 : max($positions) + 1;
        } else {
            foreach ($positions as $key => $current) {
                if ($current >= $position) {
                    $this->formValues['sections'][$key]['position'] = $current + 1;
                }
            }
        }

        // Same name the delete buttons send, so removed indexes are never reused.
        $this->addCollectionItem($propertyAccessor, $this->formName.'[sections]');
        $index = array_key_last($this->formValues['sections']);
        $this->formValues['sections'][$index]['type'] = $type->value;
        $this->formValues['sections'][$index]['position'] = $position;
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChapterType::class, $this->initialFormData);
    }
}
