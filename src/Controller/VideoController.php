<?php

namespace App\Controller;
//1 - D'abord j'utilise en 'use'
//2 - Après je passe en paramètre de ces méthodes
//3 - Après je peux me servir dans me méthodes
use App\Entity\Video;
use App\Entity\User;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Form\VideoFormType;


class VideoController extends AbstractController
{
    #[Route('/app', name: 'dashboard', methods: ['GET'])]
    public function index(VideoRepository $videoRepository): Response
    {
        return $this->render('video/index.html.twig', [
            'videos' => $videoRepository->findAll(),
        ]);
    }
    // Adding videos - 'thumbnail/video'
    #[Route('/app/add-video', name: 'add_video', methods: ['GET', 'POST'])]
    public function addVideo(Request $request, PersistenceManagerRegistry $doctrine, SluggerInterface $slugger ): Response
    {
        $video = new Video(); //new instance
        $form = $this->createForm(VideoFormType::class, $video); //j'ai appelé le form que vien du VideoFormType
               
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $thumbnailFile = $form->get('thumbnail')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($thumbnailFile) {
                $originalFilename = pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$thumbnailFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $thumbnailFile->move(
                        $this->getParameter('thumbnail_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $video->setThumbnail($newFilename);
            }
            $videoFile = $form->get('video')->getData();
            // Check if a file was uploaded
            if ($videoFile) {
                // Generate a unique filename
                $originalFilename = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$videoFile->guessExtension();
                // Move the file to the destination directory
                try {
                    $videoFile->move(
                        $this->getParameter('video_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $video->setVideo($newFilename);

                // Save the file name to the database or do any other necessary action              
                // Logic to handle the case where no file was uploaded
            }
            $entityManager = $doctrine->getManager();
            $entityManager -> persist($video);
            $entityManager -> flush();

            return $this->redirectToRoute('adm_videos_list');
        }
        return $this->render('video/add.html.twig',[
            'form' => $form->createView(),
        ]);
    }
    // Displaying video
    #[Route('/app/video/{id}', name: 'app_video_show', methods: ['GET'])]
    public function show(Video $video): Response
    {
        return $this->render('video/show.html.twig', [
            'video' => $video,
        ]);
    }

    #[Route('/app/saved', name: 'saved_videos', methods: ['GET'])]
    public function showSavedVideos(): Response
    {
        $liked = $this->getUser()->getLiked()->toArray();
        return $this->render('video/index.html.twig', [
            'videos' => $liked,
        ]);
    }
    
    #[Route('/app/admin/videos', name: 'adm_videos_list', methods: ['GET'])]
    public function showAdmVideos(VideoRepository $videoRepository): Response
    {   
        return $this->render('video/video_dashboard.html.twig', [
            'videos' => $videoRepository->findAll(),
        ]);
    }

    #[Route('/app/admin/videos/{id}', name: 'app_video_delete', methods: ['POST'])]
    public function delete(Request $request, Video $video, User $user, VideoRepository $videoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $videoRepository->remove($video, true);
        }

        return $this->redirectToRoute('adm_videos_list', Response::HTTP_SEE_OTHER);
    }

    #[Route('/app/admin/videos/{id}', name: 'app_video_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, Video $video, VideoRepository $videoRepository): Response
    {   
        $form = $this->createForm(VideoFormType::class, $video);
        $form->handleRequest($request);

        // Check that the form has been submitted and is valid
        if ($form->isSubmitted() && $form->isValid()) {
            $video->setName($request->request->get('name'));
            $video->setDescription($request->request->get('description'));
            
            // Save changes to the video
            $videoRepository->save($video, true);

            // Redirects to video list page
            return $this->redirectToRoute('app_video_show', [], Response::HTTP_SEE_OTHER);
        }
       
        //Render the video editing form
        return $this->render('video/edit.html.twig', [
            'form' => $form->createView(),
            'video' => $video
        ]);
        
    }
}
