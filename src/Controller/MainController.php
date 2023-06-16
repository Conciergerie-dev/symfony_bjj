<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserFormType;
use Symfony\Bundle\SecurityBundle\Security;

class MainController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
       $this->security = $security;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', ['tab' => 'saved_videos',]);
    }

    #[Route('/app/profile', name: 'profile')]
    public function profile(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->security->getUser();
        $form = $this->createForm(UserFormType::class, $user);
        $form->remove('roles'); 
        $form->remove('plainPassword');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'user' => $user,
            'create'=> false,
            'tab' => 'saved_videos',
        ]);
    }

    #[Route('/app/admin', name: 'admin')]
    public function admin(): Response
    {
        return $this->render('main/index.html.twig', ['tab' => 'saved_videos',]);
    }
}
