<?php

namespace App\Twig\Components;

use App\Entity\Chapter;
use App\Form\ChapterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
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

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ChapterType::class, $this->initialFormData);
    }
}
