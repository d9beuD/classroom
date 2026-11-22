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
    public function addSection(PropertyAccessorInterface $propertyAccessor, #[LiveArg] string $type): void
    {
        $type = ChapterSectionType::tryFrom($type) ?? throw new BadRequestHttpException('Invalid section type.');

        // Same name the delete buttons send, so removed indexes are never reused.
        $this->addCollectionItem($propertyAccessor, $this->formName.'[sections]');
        $index = array_key_last($this->formValues['sections']);
        $this->formValues['sections'][$index]['type'] = $type->value;
        $this->formValues['sections'][$index]['position'] = $index;
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChapterType::class, $this->initialFormData);
    }
}
