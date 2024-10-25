<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonFormType;
use App\Repository\LessonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class LessonController extends AbstractController
{
    #[Route('/app/lessons', name: 'app_lesson')]
    public function index(LessonRepository $lessonRepository): Response
    {
        $user = $this->getUser();
        $nextLessons = $lessonRepository->findUpcomingLessons();
        $pastLessons = $lessonRepository->findPastLessonsByUser($user);

        return $this->render('lesson/index.html.twig', [
            'user' => $user,
            'nextLessons' => $nextLessons,
            'pastLessons' => $pastLessons
        ]);
    }

    #[Route('/app/lessons/{id}/join', name: 'app_lesson_join', methods: ['POST'])]
    public function joinLesson(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $lesson->addUser($this->getUser());
        $lessonRepository->save($lesson, true);

        return $this->redirectToRoute('app_lesson');
    }

    #[Route('/app/lessons/{id}/leave', name: 'app_lesson_leave', methods: ['POST'])]
    public function leaveLesson(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $lesson->removeUser($this->getUser());
        $lessonRepository->save($lesson, true);

        return $this->redirectToRoute('app_lesson');
    }

    #[Route('/app/admin/lessons/new', name: 'add_lesson', methods: ['GET', 'POST'])]
    public function addLesson(Request $request, PersistenceManagerRegistry $doctrine): Response
    { 
        $lesson = new Lesson();
        $form = $this->createForm(LessonFormType::class, $lesson);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();
            $time = $form->get('time')->getData();

            $lesson->setDate($date);
            $lesson->setTime($time);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($lesson);
            $entityManager->flush();

            return $this->redirectToRoute('app_lesson_index');
        }
        return $this->render('lesson/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/app/admin/lessons', name: 'app_lesson_index', methods: ['GET'])]
    public function showAdmLessons(LessonRepository $lessonRepository): Response
    {   
        if($this->isGranted('ROLE_ADMIN')) {
            $lessons = $lessonRepository->findAll();
        }
        return $this->render('lesson/lesson_dashboard.html.twig', [
            'lessons' => $lessons,
        ]);
    }

    #[Route('/app/lessons/{id}', name: 'app_lesson_show', methods: ['GET'])]
     public function show(Lesson $lesson): Response
     {  
        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
        ]);
     }

     #[Route('/app/admin/lessons/{id}/edit', name: 'app_lesson_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $form = $this->createForm(LessonFormType::class, $lesson);
        $form->remove('lesson'); 
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {   
            // Save changes to the video
            $lessonRepository->save($lesson, true);
    
            // Redirects to video list page
            return $this->redirectToRoute('app_lesson_index', [], Response::HTTP_SEE_OTHER);
        }
        //Render the video editing form
        return $this->render('lesson/edit.html.twig', [
            'form' => $form->createView(),
            'lesson' => $lesson
        ]);  
    }

    #[Route('/app/admin/lessons/{id}/delete', name: 'app_lesson_delete', methods: ['POST'])]
    public function delete(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->request->get('_token'))) {
            $lessonRepository->remove($lesson, true);
        }

        return $this->redirectToRoute('app_lesson_index');        
    }
}
