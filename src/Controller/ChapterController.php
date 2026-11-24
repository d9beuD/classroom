<?php

namespace App\Controller;

use App\Dto\ChapterOrder;
use App\Entity\Chapter;
use App\Entity\Course;
use App\Entity\User;
use App\Form\ChapterType;
use App\Markdown\ChapterRenderer;
use App\Repository\ChapterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
    public function new(Course $course, Request $request, EntityManagerInterface $entityManager, ChapterRenderer $renderer): Response
    {
        $chapter = new Chapter();
        $chapter->setCourse($course);
        $author = $this->getUser();
        \assert($author instanceof User);
        $chapter->setAuthor($author);
        // New chapters go last; the course overview reorders them.
        $chapter->setPosition(max([-1, ...$course->getChapters()->map(fn (Chapter $other): int => $other->getPosition())->toArray()]) + 1);
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chapter->setRenderedContent($renderer->render($chapter));
            $entityManager->persist($chapter);
            $entityManager->flush();
            $this->addFlash('success', 'Chapitre créé.');

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
    public function edit(Request $request, Chapter $chapter, Course $course, EntityManagerInterface $entityManager, ChapterRenderer $renderer): Response
    {
        $form = $this->createForm(ChapterType::class, $chapter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chapter->setRenderedContent($renderer->render($chapter));
            $entityManager->flush();
            $this->addFlash('success', 'Chapitre enregistré.');

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

    /**
     * Saves the order of a course's chapters after a drag and drop on the course overview.
     */
    #[Route('/course/{courseSlug:course.slug}/chapters/order', name: 'app_chapter_reorder', methods: ['POST'])]
    #[IsGranted('ROLE_PROFESSOR')]
    public function reorder(Course $course, Request $request, #[MapRequestPayload] ChapterOrder $order, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('reorder-chapters-'.$course->getId(), $request->headers->get('X-CSRF-Token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $chapters = [];
        foreach ($course->getChapters() as $chapter) {
            $chapters[$chapter->getId()] = $chapter;
        }

        // The new order must list exactly this course's chapters (ids are already unique).
        if (\count($order->ids) !== \count($chapters) || array_diff($order->ids, array_keys($chapters))) {
            throw new BadRequestHttpException('The order must list every chapter of the course.');
        }

        foreach ($order->ids as $position => $id) {
            $chapters[$id]->setPosition($position);
        }
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/chapter/{id}', name: 'app_chapter_delete', methods: ['POST'])]
    #[IsGranted('ROLE_PROFESSOR')]
    public function delete(Request $request, Chapter $chapter, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chapter->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chapter);
            $entityManager->flush();
            $this->addFlash('success', 'Chapitre supprimé.');
        } else {
            $this->addFlash('danger', 'Le chapitre n\'a pas pu être supprimé, veuillez réessayer.');
        }

        return $this->redirectToRoute('app_chapter_index', [], Response::HTTP_SEE_OTHER);
    }
}
