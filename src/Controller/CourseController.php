<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\ChapterRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/course')]
final class CourseController extends AbstractController
{
    #[Route(name: 'app_course_index', methods: ['GET'])]
    public function index(CourseRepository $courseRepository): Response
    {
        return $this->render('course/index.html.twig', [
            'courses' => $courseRepository->findAllWithChapters(),
        ]);
    }

    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($course);
            $entityManager->flush();
            $this->addFlash('success', 'Cours créé.');

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{slug:course}/overview', name: 'app_course_overview', methods: ['GET'])]
    public function overview(Course $course, ChapterRepository $chapterRepository): Response
    {
        return $this->render('course/overview.html.twig', [
            'course' => $course,
            'chapters' => $chapterRepository->findBy(['course' => $course], ['position' => 'ASC']),
        ]);
    }

    #[Route('/{slug:course}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Course $course, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Cours enregistré.');

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{slug:course}', name: 'app_course_delete', methods: ['POST'])]
    public function delete(Request $request, Course $course, EntityManagerInterface $entityManager): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($course);
            $entityManager->flush();
            $this->addFlash('success', 'Cours supprimé.');
        } else {
            $this->addFlash('danger', 'Le cours n\'a pas pu être supprimé, veuillez réessayer.');
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }
}
