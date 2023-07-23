<?php

namespace App\Controller;

use App\Entity\SelfDefense;
use App\Repository\SelfDefenseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\SelfDefenseFormType;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Filesystem\Filesystem;

class SelfDefenseController extends AbstractController
{
    #[Route('/self/defense', name: 'app_self_defense')]
    public function index(Request $request, SelfDefenseRepository $selfDefenseRepository): Response
    {
        $selfdefense = $selfDefenseRepository->findAll();

        return $this->render('self_defense/index.html.twig', [
            'videos' => $selfdefense,
        ]);
    }

    // Adding videos - 'thumbnail/video'
    #[Route('/app/admin/self/new', name: 'add_self_defense', methods: ['GET', 'POST'])]
    public function addVideo(Request $request, PersistenceManagerRegistry $doctrine, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $selfDefense = new SelfDefense(); //new instance
        $form = $this->createForm(SelfDefenseFormType::class, $selfDefense); //j'ai appelé le form que vien du VideoFormType

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
                $selfDefense->setThumbnail($newFilename);
            }
            $selfDefenseFile = $form->get('video')->getData();
            // Check if a file was uploaded
            if ($selfDefenseFile) {
                // Generate a unique filename
                $originalFilename = pathinfo($selfDefenseFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $selfDefenseFile->guessExtension();
                // Move the file to the destination directory
                try {
                    $selfDefenseFile->move(
                        $this->getParameter('video_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $selfDefense->setVideo($newFilename);

                // Save the file name to the database or do any other necessary action              
                // Logic to handle the case where no file was uploaded
            }
            $entityManager = $doctrine->getManager();
            $entityManager->persist($selfDefense);
            $entityManager->flush();

            return $this->redirectToRoute('app_self_index');
        }
        return $this->render('self_defense/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Displaying video
    #[Route('/app/videos/{id}', name: 'app_self_defense_show', methods: ['GET'])]
    public function show(SelfDefense $selfDefense): Response
    {
        return $this->render('self_defense/show.html.twig', [
            'selfDefense' => $selfDefense,
        ]);
    }

    #[Route('/app/admin/self', name: 'app_self_index', methods: ['GET'])]
    public function showAdmSelfDefense(SelfDefenseRepository $selfDefenseRepository): Response
    {
        return $this->render('self_defense/self_dashboard.html.twig', [
            'selfDefenses' => $selfDefenseRepository->findAll(),
        ]);
    }

    // Editing a video
    #[Route('/app/admin/self_defense/{id}/edit', name: 'app_self_defense_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, SelfDefense $selfDefense, SelfDefenseRepository $selfDefenseRepository, 
    FileUploader $fileUploader, FileSystem $filesystem): Response 
    {
        $form = $this->createForm(SelfDefenseFormType::class, $selfDefense);
        $form->remove('video');
        $form->handleRequest($request);

        // Check that the form has been submitted and is valid
        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnailFile = $form->get('thumbnail')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($thumbnailFile) {
                $newFilename = $fileUploader->upload($thumbnailFile);
                $filesystem->remove($this->getParameter('thumbnail_directory') . '/' . $selfDefense->getThumbnail());

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $selfDefense->setThumbnail($newFilename);
            }
            // Save changes to the video
            $selfDefenseRepository->save($selfDefense, true);

            // Redirects to video list page
            return $this->redirectToRoute('app_self_index', [], Response::HTTP_SEE_OTHER);
        }
        //Render the video editing form
        return $this->render('self_defense/edit.html.twig', [
            'form' => $form->createView(),
            'video' => $selfDefense
        ]);
    }

     //Deleting video 
     #[Route('/app/admin/self_defense/{id}/delete', name: 'app_self_defense_delete', methods: ['POST'])]
     public function delete(Request $request, SelfDefense $selfDefense, SelfDefenseRepository $selfDefenseRepository, Filesystem $filesystem): Response
     {
         if ($this->isCsrfTokenValid('delete'.$selfDefense->getId(), $request->request->get('_token'))) {
             $selfDefenseRepository->remove($selfDefense, true);
 
             $selfDefenseFile = $selfDefense->getVideo();
             $thumbnailFile = $selfDefense->getThumbnail();
             
             //Delete video
             if ($selfDefenseFile) {
                 $selfDefenseFilePath = $this->getParameter('video_directory') . '/' . $selfDefenseFile;
                 if ($filesystem->exists($selfDefenseFilePath)) {
                     $filesystem->remove($selfDefenseFilePath);
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
 
         return $this->redirectToRoute('app_self_index');        
     }
}
