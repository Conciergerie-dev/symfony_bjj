<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\SearchFormType;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\VideoFormType;
use App\Service\FileUploader;
use Symfony\Component\Filesystem\Filesystem;

class VideoController extends AbstractController
{
    #[Route('/app', name: 'dashboard', methods: ['GET', 'POST'])]
    public function index(Request $request, VideoRepository $videoRepository): Response
    {
        $form = $this->createForm(SearchFormType::class);
        $videos = $videoRepository->findBy(['category' => 'bjj']);
        $criteria = [
            'basePosition' => '',
            'endingPosition' => '',
        ];

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $videos = $videoRepository->search($criteria);
        }

        return $this->render('video/index.html.twig', [

            'form' => $form->createView(),
            'criteria' => $criteria,
            'videos' => $videos,
        ]);
    }

    // Adding videos - 'thumbnail/video'
    #[Route('/app/admin/videos/new', name: 'add_video', methods: ['GET', 'POST'])]
    public function addVideo(Request $request, PersistenceManagerRegistry $doctrine, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $video = new Video(); //new instance
        $form = $this->createForm(VideoFormType::class, $video); //j'ai appelé le form que vien du VideoFormType

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnailFile = $form->get('thumbnail')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($thumbnailFile) {
                $newFilename = $fileUploader->upload($thumbnailFile);

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
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $videoFile->guessExtension();
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
            $time = date('d-m-Y');
            $video->setDate(new \DateTime($time));
            $video->setCategory('bjj');
            $entityManager = $doctrine->getManager();
            $entityManager->persist($video);
            $entityManager->flush();

            return $this->redirectToRoute('app_video_index');
        }
        return $this->render('video/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Displaying video
    #[Route('/app/videos/{id}', name: 'app_video_show', methods: ['GET'])]
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

    #[Route('/app/saved/{id}', name: 'app_liked_video', methods: ['POST'])]
    public function saveLikedVideo(Request $request, Video $video, VideoRepository $videoRepository): Response
    {
        $video->addLiker($this->getUser());
        $videoRepository->save($video, true);

        return $this->redirectToRoute('app_video_show', array('id' => $video->getId()));
    }

    #[Route('/app/saved/{id}/delete', name: 'app_liked_delete', methods: ['POST'])]
    public function deleteLikedVideo(Request $request, Video $video, VideoRepository $videoRepository): Response
    {
        $video->removeLiker($this->getUser());
        $videoRepository->save($video, true);

        return $this->redirectToRoute('app_video_show', array('id' => $video->getId()));
    }

    #[Route('/app/admin/videos', name: 'app_video_index', methods: ['GET'])]
    public function showAdmVideos(VideoRepository $videoRepository): Response
    {
        return $this->render('video/video_dashboard.html.twig', [
            'videos' => $videoRepository->findBy(['category' => 'bjj']),
        ]);
    }

    // Editing a video
    #[Route('/app/admin/videos/{id}/edit', name: 'app_video_edit', methods: ['POST', 'GET'])]
    public function edit(
        Request $request,
        Video $video,
        VideoRepository $videoRepository,
        FileUploader $fileUploader,
        FileSystem $filesystem
    ): Response {
        $form = $this->createForm(VideoFormType::class, $video);
        $form->remove('video');
        $form->handleRequest($request);

        // Check that the form has been submitted and is valid
        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnailFile = $form->get('thumbnail')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($thumbnailFile) {
                $newFilename = $fileUploader->upload($thumbnailFile);
                $filesystem->remove($this->getParameter('thumbnail_directory') . '/' . $video->getThumbnail());

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $video->setThumbnail($newFilename);
            }
            // Save changes to the video
            $videoRepository->save($video, true);

            // Redirects to video list page
            return $this->redirectToRoute('app_video_index', [], Response::HTTP_SEE_OTHER);
        }
        //Render the video editing form
        return $this->render('video/edit.html.twig', [
            'form' => $form->createView(),
            'video' => $video
        ]);
    }

    //Deleting video 
    #[Route('/app/admin/videos/{id}/delete', name: 'app_video_delete', methods: ['POST'])]
    public function delete(Request $request, Video $video, VideoRepository $videoRepository, Filesystem $filesystem): Response
    {
        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->request->get('_token'))) {
            $videoRepository->remove($video, true);

            $videoFile = $video->getVideo();
            $thumbnailFile = $video->getThumbnail();

            //Delete video
            if ($videoFile) {
                $videoFilePath = $this->getParameter('video_directory') . '/' . $videoFile;
                if ($filesystem->exists($videoFilePath)) {
                    $filesystem->remove($videoFilePath);
                }
            }

            // Delete thumbnail
            if ($thumbnailFile) {
                $thumbnailFilePath = $this->getParameter('thumbnail_directory') . '/' . $thumbnailFile;
                if ($filesystem->exists($thumbnailFilePath)) {
                    $filesystem->remove($thumbnailFilePath);
                }
            }
        }

        return $this->redirectToRoute('app_video_index');
    }

}
