<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Security("is_granted('ROLE_USER')")]
class UserProfileController extends AbstractController
{
    #[Route('/user/profile', name: 'app_user_profile')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $message = null;
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        $currentValues = [
            'location' => $user->getLocation(),
            'department' => $user->getDepartment(),
            'phone' => $user->getPhone(),
            'extension' => $user->getExtension(),
            'title' => $user->getTitle(),
            'homepage' => $user->getHomepage(),
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // @todo Check in admin settings which fields are set to display for users
            foreach ($data as $item) {
                $user->setLocation($item['location']);
                $user->setDepartment($item['department']);
                $user->setPhone($item['phone']);
                $user->setExtension($item['extension']);
                $user->setTitle($item['title']);
                $user->setHomepage($item['homepage']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Successfully saved settings.');

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user_profile/profile.html.twig', [
            'userProfileForm' => $form,
            'currentValues' => $currentValues,
            'message' => $message
        ]);
    }

    #[Route('/user/changepassword', name: 'app_user_profile_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $message = null;
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if current password is correct
            // dd($userPasswordHasher->isPasswordValid($user, $form->get('password')->getData()));
            if ($userPasswordHasher->isPasswordValid($user, $form->get('password')->getData())) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_profile');
            }

            $this->addFlash('notice', 'Current password is incorrect.');
        }

        return $this->render('user_profile/changepassword.html.twig', [
            'changePasswordForm' => $form->createView(),
            'message' => $message
        ]);
    }
}
