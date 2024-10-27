<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserFormType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\SendMailService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\ResetPasswordRequestFormType;
use App\Entity\User;

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
        return $this->render('main/index.html.twig');
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
        ]);
    }

    #[Route('/app/admin', name: 'admin')]
    public function admin(): Response
    {
        return $this->render('main/admin.html.twig');
    }

    //Password change
    #[Route('/app/profile/modify-password', name: 'modify_password')]
    public function modifyPassword(Request $request, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        $user = $this->security->getUser(); 
        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($encodedPassword);
            $userRepository->save($user, true);
           
           
            return $this->redirectToRoute('dashboard');
        }
        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    //Forgot password
    #[Route('/app/profile/forgotten-password', name: 'forgotten_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, UserRepository $usersRepository, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager, SendMailService $mail): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //On va chercher l'utilisateur par son email
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());

            // On vérifie si on a un utilisateur
            if($user){
                // On génère un token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // On génère un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // On crée les données du mail
                $context = compact('url', 'user');

                // Envoi du mail
                $mail->send(
                    'no-reply@e-commerce.com',
                    $user->getEmail(),
                    'Réinitialisation de mot de passe',
                    'password_reset',
                    $context
                );

                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            }
            // $user est null
            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/forgotten_password.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    //Creating a new route for a new user
    #[Route('/new-user', name: 'user_new')]
        public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
        {   
            $user = new User();
            $form = $this->createForm(UserFormType::class, $user);
            $form->remove('roles'); 
            $form->remove('belt'); 
            $form->handleRequest($request);
            // dd($form);
            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                
                $entityManager->persist($user);
                $entityManager->flush();
                // do anything else you need here, like send an email

                return $this->redirectToRoute('app_login');
            }

            return $this->render('registration/new_user.html.twig', [
                'registrationForm' => $form->createView(),
                'create' => true,
            ]);
        }
}
