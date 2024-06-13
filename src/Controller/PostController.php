<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\PostType;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PostController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
       $this->security = $security;
    }
   

    public function nbre($arr):int{
        return count($arr);
    }
    #[Route('/post', name: 'app_post_list')]
    public function index(PostRepository $postRepository): Response
    {
        
        $posts = $postRepository->findBy(['isActive' => true]);
        $commentsFilter = [
            "bad", "worse", "frog", "yellow", "pink", "bye"
        ];
        foreach ($posts as $post) {
            foreach ($post->getComments() as $comment) {
                $commentFiltered = $comment->getDescription();
                foreach ($commentsFilter as $filter) {
                    $commentFiltered = str_replace($filter, "******", $commentFiltered);
                }
                $comment->setDescription($commentFiltered);
            }
        } 
        usort($posts, function($a, $b){
            return count($b->getComments()) - count($a->getComments());
        });
        //resultat post:$posts
        return $this->render(
            'post/index.html.twig',
            array('listepost' => $posts)
        );
    }

    // affiche un post par id 
    #[Route('/post/{id}', name: 'app_post_afficher')]
    public function show(Post $postObj, Request $request, ManagerRegistry $mg, PostLikeRepository $PostLikeRepository, UserRepository $userRepository)
    {
        

        /*$gamer=$userRepository->find($id);
        $gamer->getUsername();
        $gamer = $this->getUser();
        $comment->setUser($gamer);*/
        
        $form = $this->createForm(CommentType::class);

        $form->handleRequest($request);
        $like = $PostLikeRepository->findBy(['post'=>$postObj->getId()]) ?  true : false;
        
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = new Comment();
            $comment = $form->getData();
            $comment->setPost($postObj);
            $comment->setUser($this->security->getUser());

            // Enregistrer les modifications dans la base de données
            $entityManager = $mg->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            // Rediriger vers la page de liste de la publication
            return $this->redirectToRoute('app_post_afficher', ['id' => $postObj->getId()]);
        }

        $commentsFilter = [
            "bad", "worse", "frog", "yellow", "pink", "bye"
        ];
        foreach ($postObj->getComments() as $comment) {
            $commentFiltered = $comment->getDescription();
            foreach ($commentsFilter as $filter) {
                $commentFiltered = str_replace($filter, "******", $commentFiltered);
            }
            $comment->setDescription($commentFiltered);
        }
        return $this->render('post/show.html.twig', array(
            'like'=>$like,
            'post' => $postObj,
            //'user'=>$gamer,
            'form' => $form->createView()
        ));
    }

    //ajout post
    #[Route('/ajouterr', name: 'app_post_ajouter')]
    public function Ajouterpost(Request $request, PostRepository $postRepository, UserRepository $userRepository , ManagerRegistry $mg): Response
    {
        // Créer une nouvelle instance de Post
        $post = new Post();
       $user=$this->getUser()->getUsername();
        $gamer=$userRepository->findOneBy(array('username' =>$user),null,1,0);
        $post->setUser($gamer);
        // Créer un formulaire pour la publication
        $form = $this->createForm(PostType::class, $post);
        // Traiter la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setIsActive(true);
            $brochureFile = $form->get('image')->getData();
            if ($brochureFile) {
                // ceci est nécessaire pour inclure en toute sécurité le nom du fichier dans l'URL
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            }
            // cette condition est nécessaire car le champ 'brochure' n'est pas obligatoire
            // donc le fichier PDF doit être traité uniquement lorsqu'un fichier est téléchargé
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // ceci est nécessaire pour inclure en toute sécurité le nom du fichier dans l'URL
                $safeFilename = $post->setImage($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();
                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $post->setImage($newFilename);
            }
            $entityManager = $mg->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('app_post_list');
        }
        return $this->renderForm('post/ajouter.html.twig', [
            'post' => $post,
            'form' => $form,
            'user'=>$gamer
        ]);
    }

    #[Route('/{id}/modifierr', name: 'app_post_modifier')]
    public function modifier(Request $request, Post $post, ManagerRegistry $mg): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $mg->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('app_post_list');
        }
        return $this->renderForm('post/modifier.html.twig', [
            'modifierpost' => $post,
            'form' => $form,
        ]);
    }
    //supprime post
    #[Route('/removepost/{id}', name: 'app_post_remove')]
    public function remove(ManagerRegistry $mg, PostRepository $postRepository, $id, Request $request): Response
    {
        $post = $postRepository->find($id);
        $sem = $mg->getManager();
        $comments = $post->getComments();
        foreach ($comments as $comment) {
            $sem->remove($comment);
        }
        $sem->remove($post);
        $sem->flush();
        return $this->redirectToRoute('app_post_list');
    }
    
    #[Route('/post/{id}/like', name: 'post_like')]
    public function like(Request $request,Post $post,ManagerRegistry $mg ): Response
    {
        $like = new PostLike();
        $like->setPost($post);
        $like->setUser($this->security->getUser());
        $entityManager=$mg->getManager();
        $entityManager->persist($like);
        $entityManager->flush();
        
        
        return $this->redirectToRoute('app_post_afficher',['id'=>$post->getId()]);
    }

    #[Route('/post/{id}/dislike', name: 'post_dislike')]
    public function disLike(Post $post,ManagerRegistry $mg, PostLikeRepository $PostLikeRepository ): Response
    {
        $entityManager=$mg->getManager();
        $entityManager->remove($PostLikeRepository->findOneBy(['post'=>$post->getId()]));
        $entityManager->flush();
       
        return $this->redirectToRoute('app_post_afficher',['id'=>$post->getId()]);
    }
    
}
