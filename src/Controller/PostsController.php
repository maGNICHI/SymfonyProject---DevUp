<?php

namespace App\Controller;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class PostsController extends AbstractController
{
    private $logger;
    #[Route('/postst', name: "listPostJSON")]
    public function index(PostRepository $postRepository, SerializerInterface $serializer): Response
    {
        $posts = $postRepository->findAll();
        $json = $serializer->serialize($posts, 'json', ['groups' => 'posts']);
        return new Response($json);
    }
    #[Route('/addP', name: 'app_post_add_json')]
    public function addPostJSON(Request $request, NormalizerInterface $Normalizer, EntityManagerInterface $em)
    {
        $em = $this->getDoctrine()->getManager();
        $Post = new Post();
        $Post->setTitle($request->get("title"));
        $Post->setDescription($request->get("description"));
        $Post->setImage($request->get("image"));
        /*$user = $this->getUser();
        $Post->setUser($user);*/
        $em->persist($Post);
        $em->flush();
        $jsonContent = $Normalizer->normalize($Post, 'json', ['groups' => 'posts']);
        return new Response(json_encode($jsonContent));
    }
    #[Route('/update/{id}', name: 'app_post_json')]
    public function modifierPostAction(Request $request, $id, NormalizerInterface $Normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $Post = $em->getRepository(Post::class)->find($id);
        $Post->setTitle($request->get("title"));
        $Post->setDescription($request->get("description"));
        $em->persist($Post);
        $em->flush();
        $jsonContent = $Normalizer->normalize($Post, 'json', ['groups' => 'posts']);
        return new Response("User updated successfully " . json_encode($jsonContent));
    }
    #[Route('/deletePostJSON/{id}', name: 'deletePostJSON')]
    public function deleteStudentJSON(Request $req, $id, NormalizerInterface $Normalizer)
    {

        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        $em->remove($post);
        $em->flush();
        $jsonContent = $Normalizer->normalize($post, 'json', ['groups' => 'posts']);
        return new Response("Post deleted successfully " . json_encode($jsonContent));
    }
    #[Route('/addComment', name: 'app_comment_add_json')]
    public function ajouterCommentaireAction(Request $request, EntityManagerInterface $em)
    {
        $comment = new Comment();
        $description = $request->query->get("description");
        $post = $request->query->get("idpost");
        $em = $this->getDoctrine()->getManager();

        $comment->setPost($this->getDoctrine()->getManager()->getRepository(Post::class)->find($post));
        $comment->setDescription($description);

        $em->persist($comment);
        $em->flush();

        return $this->json($comment, 200, [], ['groups' => 'comment']);
    }
    #[Route('/comments/{id}', name: "listcommentJSON")]
    public function AfficheComment($id, Request $request, CommentRepository $cmtRepository)
    {

        $Commentaire = $cmtRepository->findBy(array('post' => $id));

        return $this->json($Commentaire, 200, [], ['groups' => 'comment']);
    }
    #[Route('/comments', name: "listcomtJSON")]
    public function Affiche2Comment()
    {
        $Commentaire = $this->getDoctrine()->getManager()->getRepository(Comment::class)->findAll();

        return $this->json($Commentaire, 200, [], ['groups' => 'comment']);
    }
    #[Route('/nblike/{id}', name: 'nblikePostJSON')]
    public function nblikejson($id, PostLikeRepository $likeRepository, Request $request): Response
    {

        $post = $likeRepository->findBy(array('post' => $id));
        return $this->json($likeRepository->count(['post' => $post]));
    }
    #[Route('/deleteComment/{id}', name: 'app_commentdelete_json')]
    public function deleteComment(Request $request, NormalizerInterface $normalizerInterface, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository(Comment::class)->find($id);
        $em->remove($comment);
        $em->flush();
        $jsonContent = $normalizerInterface->normalize($comment, 'json', ['groups' => 'comment']);
        return new Response('Produit deleted to suuccefully' . json_encode($jsonContent));
    }
    #[Route('/updatePost/{id}', name: 'app_postupdate_json')]
    public function updatePost(Request $request, NormalizerInterface $normalizerInterface, $id)
    {
        $data = $request->getContent();
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        $mydata = array();
        parse_str($data, $mydata);
        $this->logger->info($data);
        var_dump($request->getContent());
        $post->setTitle($mydata['title']);
        $post->setDescription($mydata['description']);
        $post->setImage($mydata['image']);
        $em->flush();

        $jsonContent = $normalizerInterface->normalize($post, 'json', ['groups' => 'posts']);
        return new Response("post updated successfully" . json_encode($jsonContent));
    }


    /*
     
   public function like(Post $post , EntityManagerInterface $manager, PostLikeRepository $likeRepository ):Response
   {
         $user=$this->getUser();
         if (!$user) return $this->json(['code'=>403,'message'=>"unauthorized"],403);

         if ($post->isLikeByUser($user)){
             $like=$likeRepository->findOneBy(['post'=>$post , 'Client'=>$user]);
             $manager->remove($like);
             $manager->flush();

             return $this->json([
                 'code'=>200,
                 'message'=>'Like bien supprimé',
                 'likes' => $likeRepository->count(['post'=>$post])
             ],200);
         }

         $like= new PostLike();
         $like->setPost($post)->setClient($user);
         $manager->persist($like);
         $manager->flush();

         return $this->json(['code'=> 200 ,
             'message'=> 'Like bien ajoutee',
             'likes'=>$likeRepository->count(['post'=>$post])
         ],200);

   }
    */
}
