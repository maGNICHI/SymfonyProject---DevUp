<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/posts', name: 'app_dashboard_posts')]
    public function getAllPosts(PostRepository $postRepository): Response
    {
        $posts=$postRepository->findAll();
        return $this->render('dashboard/post/index.html.twig', [
            'listepost'=>$posts,
        ]);
    }

    #[Route('/posts/active/{id}', name: 'is_active_post')]
    public function setPostToActive(PostRepository $postRepository,$id,ManagerRegistry $mg)
    {
        $post=$postRepository->find($id);
        if ($post->isIsActive())
            $post->setIsActive(false);
        else
            $post->setIsActive(true);
        $sem=$mg->getManager();
        $sem-> persist($post);
        $sem->flush();
        return $this->redirectToRoute('app_dashboard_posts');
    }

    #[Route('/stat_posts', name: 'app_posts_stats')]
    public function posts_statistiques(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        $statistiques = [];
        foreach($posts as $p){
            $post = [
                "title" => $p->getTitle(),
                "description" => $p->getDescription(),
                "nbreComments" => count($p->getComments())
            ];
            array_push($statistiques, $post);
        }
        return $this->render('dashboard/post/statistiques.html.twig', [
            'controller_name' => 'DashboardController',
            'posts' => $statistiques
        ]);
    }

    #[Route('/{id}', name: 'app_posts_show', methods: ['GET'])]
    public function show(Post $posts): Response
    {
          $commentsFilter = [
            "blue", "red", "green", "yellow", "pink", "bye"
        ];
        foreach ($posts->getComments() as $comment) {
            $commentFiltered = $comment->getDescription();
            foreach ($commentsFilter as $filter) {
                $commentFiltered = str_replace($filter, "******", $commentFiltered);
            }
            $comment->setDescription($commentFiltered);
        }
        return $this->render('dashboard/post/show.html.twig', [
            'post' => $posts,
        ]);
    }

   
}
