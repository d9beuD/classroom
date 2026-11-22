<?php

namespace App\Controller;

use App\Entity\Chapter;
use App\Entity\Course;
use App\Form\ChapterType;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('')]
final class ChapterController extends AbstractController
{
    #[Route('/chapter', name: 'app_chapter_index', methods: ['GET'])]
    public function index(ChapterRepository $chapterRepository): Response
    {
        return $this->render('chapter/index.html.twig', [
            'chapters' => $chapterRepository->findAll(),
        ]);
    }

    #[Route('/course/{courseSlug:course.slug}/chapter/new', name: 'app_chapter_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROFESSOR')]
    public function new(Course $course, Request $request, EntityManagerInterface $entityManager): Response
    {
        $chapter = new Chapter();
        $chapter->setCourse($course);
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($chapter);
            $entityManager->flush();

            return $this->redirectToRoute('app_chapter_show', [
                'chapterSlug' => $chapter->getSlug(),
                'courseSlug' => $course->getSlug(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chapter/new.html.twig', [
            'course' => $course,
            'chapter' => $chapter,
            'form' => $form,
        ]);
    }

    #[Route('/course/{courseSlug:course.slug}/chapter/{chapterSlug:chapter.slug}', name: 'app_chapter_show', methods: ['GET'])]
    public function show(Chapter $chapter, Course $course): Response
    {
        return $this->render('chapter/show.html.twig', [
            'course' => $course,
            'chapter' => $chapter,
        ]);
    }

    #[Route('/course/{courseSlug:course.slug}/chapter/{chapterSlug:chapter.slug}/edit', name: 'app_chapter_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROFESSOR')]
    public function edit(Request $request, Chapter $chapter, Course $course, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_chapter_show', [
                'chapterSlug' => $chapter->getSlug(),
                'courseSlug' => $course->getSlug(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chapter/edit.html.twig', [
            'course' => $course,
            'chapter' => $chapter,
            'form' => $form,
        ]);
    }

    #[Route('/chapter/{id}', name: 'app_chapter_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROFESSOR')]
    public function delete(Request $request, Chapter $chapter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chapter->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chapter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_chapter_index', [], Response::HTTP_SEE_OTHER);
    }
}
