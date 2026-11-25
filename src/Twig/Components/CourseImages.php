<?php

namespace App\Twig\Components;

use App\Entity\Course;
use App\Storage\CourseImageStorage;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\Upload\Form\UploadType;
use Symfony\UX\Upload\LiveComponent\Attribute\UploadTarget;
use Symfony\UX\Upload\LiveComponent\ComponentWithUploadTrait;

/**
 * A dropzone that stores each dropped image in the course, then the course's images; clicking
 * one copies its Markdown to the clipboard.
 */
#[AsLiveComponent]
final class CourseImages
{
    use ComponentWithUploadTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public Course $course;

    /** Signed token of the upload being stored; empty between two drops. */
    #[LiveProp]
    #[UploadTarget(uploader: 'course_images')]
    public ?string $image = null;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly CourseImageStorage $storage,
    ) {
    }

    /**
     * Called by the live-upload Stimulus bridge (instead of applyUpload) once an image is uploaded.
     */
    #[LiveAction]
    #[IsGranted('ROLE_PROFESSOR')]
    public function addImage(#[LiveArg] string $property, #[LiveArg] string $token): void
    {
        $this->applyUpload($property, $token);
        $upload = $this->getUpload('image');
        if (null === $upload) {
            return;
        }

        $this->storage->store($this->course, $upload, $upload->getClientOriginalName(), $upload->getId());
        $this->clearUpload('image');
    }

    public function getUploadForm(): FormView
    {
        return $this->formFactory->createNamedBuilder('course_images')
            ->add('image', UploadType::class, [
                'label' => 'Ajouter une image',
                'uploader' => 'course_images',
                'layout' => 'dropzone',
                'required' => false,
                'data' => $this->getUpload('image'),
            ])
            ->getForm()
            ->createView();
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    public function getImages(): array
    {
        return $this->storage->list($this->course);
    }
}
