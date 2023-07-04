<?php

namespace App\Controller;

use App\Entity\Playlist;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PlaylistRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\FileUploader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use App\Form\PlaylistFormType;


class PlaylistController extends AbstractController
{   
    //The index will be for users
    #[Route('/app/playlist', name: 'app_playlist')]
    public function index(PlaylistRepository $playlistRepository): Response
    {   
        return $this->render('playlist/index.html.twig', [
            'playlists' => $playlistRepository->findAll(),
        ]);
    }
    
    //Dashboard page displays for the admin all the playlists
    #[Route('/app/admin/playlists', name: 'app_playlist_index', methods: ['GET'])]
    public function showAdmPlaylists(PlaylistRepository $playlistRepository): Response
    {   
        // dd($playlistRepository->findAll());
        return $this->render('playlist/playlist_dashboard.html.twig', [
            'playlists' => $playlistRepository->findAll(),
        ]);
    }

     // Displaying the playlist
     #[Route('/app/playlist/{id}', name: 'app_playlist_show', methods: ['GET'])]
     public function show(Playlist $playlist): Response
     {  
         return $this->render('playlist/show.html.twig', [
             'playlist' => $playlist,
         ]);
     }

    // Adding a playlist
    #[Route('/app/admin/playlists/add', name: 'add_playlist', methods: ['GET', 'POST'])]
    public function addPlaylist(Request $request, PersistenceManagerRegistry $doctrine, SluggerInterface $slugger, FileUploader $fileUploader ): Response
    {
        $playlist = new Playlist(); //new instance
        $form = $this->createForm(PlaylistFormType::class, $playlist);
        // $form->remove('videos'); 

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $thumbnailFile = $form->get('thumbnail')->getData();
            if ($thumbnailFile) {
                $newFilename = $fileUploader->upload($thumbnailFile);

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $playlist->setThumbnail($newFilename);
            }
        
            $entityManager = $doctrine->getManager();
            $entityManager -> persist($playlist);
            $entityManager -> flush();

            return $this->redirectToRoute('app_playlist_index');
        }
        return $this->render('playlist/add.html.twig', [
            'form' => $form,       
        ]);
    }

    // Editing the playlist
    #[Route('/app/admin/playlists/{id}/edit', name: 'app_playlist_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, Playlist $playlist, PlaylistRepository $playlistRepository, 
    FileUploader $fileUploader, FileSystem $filesystem): Response
    {
        $form = $this->createForm(PlaylistFormType::class, $playlist);
        $form->remove('playlist'); 
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {   
            $thumbnailFile = $form->get('thumbnail')->getData();
    
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($thumbnailFile) {
                $newFilename = $fileUploader->upload($thumbnailFile);
                $filesystem->remove($this->getParameter('thumbnail_directory').'/'.$playlist->getThumbnail());
    
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $playlist->setThumbnail($newFilename);
            }   
            // Save changes to the video
            $playlistRepository->save($playlist, true);
    
            // Redirects to video list page
            return $this->redirectToRoute('app_playlist_index', [], Response::HTTP_SEE_OTHER);
        }
        //Render the video editing form
        return $this->render('playlist/edit.html.twig', [
            'form' => $form->createView(),
            'playlist' => $playlist
        ]);
           
    }
    
     //Deleting playlist 
     #[Route('/app/admin/playlists/{id}/delete', name: 'app_playlist_delete', methods: ['POST'])]
     public function delete(Request $request, Playlist $playlist, PlaylistRepository $playlistRepository, Filesystem $filesystem): Response
     {
         if ($this->isCsrfTokenValid('delete'.$playlist->getId(), $request->request->get('_token'))) {
             $playlistRepository->remove($playlist, true);
           
             $thumbnailFile = $playlist->getThumbnail();
              
             // Delete thumbnail
             if ($thumbnailFile) {
                 $thumbnailFilePath = $this->getParameter('thumbnail_directory') . '/' . $thumbnailFile;
                 if ($filesystem->exists($thumbnailFilePath)) {
                     $filesystem->remove($thumbnailFilePath);
                 }
             }
         }
 
         return $this->redirectToRoute('app_playlist_index');        
     }


}
   

