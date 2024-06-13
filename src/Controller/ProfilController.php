<?php

namespace App\Controller;

use App\Form\PasswordType;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
    #[Route('/profil/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ManagerRegistry $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('message', 'Profile Updated');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
   /* #[Route('/profile/passedit', name: 'app_pass_edit', methods: ['GET', 'POST'])]
    public function editPass(Request $request,ManagerRegistry $em, UserPasswordHasherInterface $userPasswordHasher)
    {
         // Get the current user
         $user = $this->getUser();
        
         // Validate the input
         $form = $this->createForm(PasswordType::class, $user);
         $form->handleRequest($request);
         
         if ($form->isSubmitted() && $form->isValid()) {
             // Encode the new password
             $newPassword = $form->get('newPassword')->getData();
             $encodedPassword = $userPasswordHasher->hashPassword($user, $newPassword);
             $user->setPassword($encodedPassword);
             
             // Save the updated user
             $em = $this->getDoctrine()->getManager();
             $em->persist($user);
             $em->flush();
             
             // Redirect to the user profile page
             return $this->redirectToRoute('app_profile');
            }
        return $this->render('profil/editpass.html.twig', [
            'form' => $form->createView(),
        ]);
    }*/
}
