<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Form\UserWithoutPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {

        $users = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/afficher', name: 'app_user_afficher', methods: ['GET'])]
    public function afficher(UserRepository $userRepository): Response
    {
        return $this->render('user/afficher.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('img')->getData();

            if ($file instanceof UploadedFile) {
                $destinationPath = $this->getParameter('kernel.project_dir') . '/public/images';
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($destinationPath, $fileName);
                $user->setImg($fileName);
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/neww', name: 'app_user_neww', methods: ['GET', 'POST'])]
    public function neww(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/neww.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);

    }

    #[Route('/show/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserWithoutPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le fichier envoyé via le formulaire
            $file = $form->get('img')->getData();

            // Vérifier si un nouveau fichier a été envoyé
            if ($file instanceof UploadedFile) {
                // Générer un nom de fichier unique
                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                // Déplacer le fichier vers l'emplacement souhaité
                $destinationPath = $this->getParameter('kernel.project_dir') . '/public/images';
                $file->move($destinationPath, $fileName);

                // Mettre à jour le chemin de l'image dans l'entité User
                $user->setImg($fileName);
            }

            // Enregistrer les modifications de l'utilisateur
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/delete/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    private function getFilteredAndSortedUsers(Request $request, UserRepository $userRepository): array
    {
        // Récupérer le terme de recherche depuis la requête
        $query = $request->query->get('q');

        // Rechercher les utilisateurs par nom si un terme de recherche est fourni
        if ($query) {
            $users = $userRepository->findByNom($query);
        } else {
            // Si aucun terme de recherche n'est fourni, récupérer tous les utilisateurs
            $users = $userRepository->findAll();
        }

        // Trier les utilisateurs
        $users = $this->sortUsers($request, $users);

        return $users;
    }

    /**
     * Trier les utilisateurs en fonction de la direction de tri spécifiée dans la requête.
     */
    private function sortUsers(Request $request, array $users): array
    {
        // Récupérer la direction de tri depuis la requête
        $sortDirection = $request->query->get('sort', 'asc');

        // Trier les utilisateurs en fonction de la direction de tri spécifiée
        if ($sortDirection === 'desc') {
            usort($users, function ($a, $b) {
                return strcmp($b->getNom(), $a->getNom());
            });
        } else {
            usort($users, function ($a, $b) {
                return strcmp($a->getNom(), $b->getNom());
            });
        }

        return $users;
    }

    #[Route('/resetPassword/profil', name: 'reset_password_profil')]
    public function resetPasswordProfile(Request $request, EntityManagerInterface $entityManager ,UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class,$user);
        $form->handleRequest($request);
        $data = $form->getData();


        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $userPasswordHasher->hashPassword($user, $data->getPassword());
            $user->setPassword($hashedPassword);
            $user->setResetCode(null);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Mot de passe changer avec succéss.'
            );

            return $this->redirectToRoute('app_myprofile');
        }

        return $this->render('user/verify_reset_code.html.twig', [
            'form' => $form->createView(),
            'isBackTemplate' => false,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                $form->get('email')->addError(new FormError('This email is already registered.'));
                return $this->render('user/register.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $file = $form->get('img')->getData();
            if($file) {
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // Handle file exception
                }
                $user->setImg($fileName);
            } else {
                $user->setImg("NoImage.jpg");
            }
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/unblock-user/{email}', name: 'app_unblock_user')]
    public function unblockUser(string $email, Request $request): Response
    {

        // Find the user by token and email
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([ 'email' => $email]);

        if (!$user) {
            // Handle case when user is not found
            throw $this->createNotFoundException('User not found');
        }

        // Unblock the user
        $user->setBloque(false);

        // Save changes to the database
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Redirect the user to the login page
        return $this->redirectToRoute('app_login');
    }



    #[Route('/admin/resetPassword/profil', name: 'reset_password_profil_admin')]
    public function resetPasswordProfileAdmin(Request $request, EntityManagerInterface $entityManager ,UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class,$user);
        $form->handleRequest($request);
        $data = $form->getData();


        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $userPasswordHasher->hashPassword($user, $data->getPassword());
            $user->setPassword($hashedPassword);
            $user->setResetCode(null);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Mot de passe changer avec succéss.'
            );

            return $this->redirectToRoute('app_myprofile_admin');
        }

        return $this->render('user/verify_reset_code.html.twig', [
            'form' => $form->createView(),
            'isBackTemplate' => true,
        ]);
    }
    #[Route('/admin/MonProfile', name: 'app_myprofile_admin')]
    public function MyprofileAdmin(UserRepository $userRepository): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
            'isBackTemplate' => true,
        ]);
    }
    #[Route('/MonProfile', name: 'app_myprofile')]
    public function Myprofile(): Response
    {

        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
            'isBackTemplate' => false,
        ]);
    }

    private function generateResetCode()
    {
        // Generate a unique reset code (you can customize the logic)
        return uniqid();
    }

    #[Route('/reset-password', name: 'reset_password')]
    public function resetPassword(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->render('user/reset_password.html.twig', []);
    }
    #[Route('/reset-password/submitted', name: 'app_reset_password_submited')]
    public function resetPasswordSubmitted(Request $request, EntityManagerInterface $entityManager,UserRepository $userRepository , MailerInterface $mailer)
    {

        $toemail = $request->get('email');
        $user = $entityManager->getRepository(User::class)->getUserByEmail($toemail);

        if ($user) {
            // Generate and save the reset code
            $resetCode = $this->generateResetCode();
            $user->setResetCode($resetCode);
            $userRepository->updateUserResetCode($user,true);

            // Send the reset code to the user's email (you need to implement this)
            //create a html template for the email
            $html = '
                    <html>
                        <body>
                            <p>Bonjour utilisateur,</p>
                            <p>Quelqu\'un a demandé un lien pour changer votre mot de passe. Vous pouvez le faire via le lien ci-dessous.</p>
                            <p><a href="http://127.0.0.1:8000/user/verify-reset-code/'.$resetCode.'">Changer mon mot de passe</a></p>
                            <p>Si vous n\'avez pas effectué cette demande, veuillez ignorer cet e-mail.</p>
                            <p>Votre mot de passe ne sera pas modifié tant que vous n\'aurez pas accédé au lien ci-dessus et créé un nouveau.</p>
                        </body>
                    </html>
                ';
            $email = (new Email())
                ->from('admingmail@gmail.com')
                ->to($toemail)
                ->subject('Reset Password')
                ->html($html);
            $mailer->send($email);

            return $this->redirectToRoute('app_login');
        }
        else
        {
            $this->addFlash(
                'error',
                'Email does not exist.'
            );

            return $this->redirectToRoute('reset_password');
        }

    }

    #[Route('/verify-reset-code/{resetCode}', name: 'verify_reset_code')]
    public function verifyResetCode(Request $request, $resetCode, EntityManagerInterface $entityManager ,UserPasswordHasherInterface $userPasswordHasher)
    {
        // Find the user by the reset code
        $user = $entityManager->getRepository(User::class)->getUserByResetCode(['resetCode' => $resetCode]);
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePasswordType::class,$user);
        $form->handleRequest($request);
        $data = $form->getData();


        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $userPasswordHasher->hashPassword($user, $data->getPassword());
            $user->setPassword($hashedPassword);
            $user->setResetCode(null);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Mot de passe changer avec succéss.'
            );

            // Redirect or render a success message
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/verify_reset_code.html.twig', [
            'form' => $form->createView(),
            'isBackTemplate' => false,

        ]);
    }
    #[Route('/admin/modifier-profil', name: 'app_edit_profile_admin')]
    public function editProfileAdmin(Request $request,UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserWithoutPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('img')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $user->setImg($fileName);
            }
            $userRepository->updateUser($user, true);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_myprofile_admin');
        }

        return $this->render('user/edit_profile.html.twig', [
            'form' => $form->createView(),
            'isBackTemplate' => true,
        ]);
    }
    #[Route('/modifier-profil', name: 'app_edit_profile')]
    public function editProfile(Request $request,UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserWithoutPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('img')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $user->setImg($fileName);
            }
            $userRepository->updateUser($user, true);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('app_myprofile');
        }

        return $this->render('user/edit_profile.html.twig', [
            'form' => $form->createView(),
            'isBackTemplate' => false,

        ]);
    }
}
