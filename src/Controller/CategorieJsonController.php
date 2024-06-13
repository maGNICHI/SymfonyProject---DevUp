<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Repository\CategorieRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Categorie;

class CategorieJsonController extends AbstractController
{
    #[Route('/categorie/json', name: 'app_categorie_json')]
    public function index(): Response
    {
        return $this->render('categorie_json/index.html.twig', [
            'controller_name' => 'CategorieJsonController',
        ]);
    }
    ///affiche les categorie en fct de json 
    #[Route('/allCategorie', name: 'app_categorie_json')]
    public function getCategorie(CategorieRepository $categorieRepository, SerializerInterface $serializerInterface): Response
    {
        $categories = $categorieRepository->findAll();
        //  $categoriesNormalises=$serializerInterface($categories,'json',['groups' => "categories"]);
        //  $json=json_encode($categoriesNormalises);

        $categoriesArray = [];

        foreach ($categories as $cat) {



            $categoriesArray[] = [
                'nom' => $cat->getNom(),
                'id' => $cat->getId(),



            ];
        }


        $json = $serializerInterface->serialize($categoriesArray, 'json', ['groups' => "categories"]);
        return  new Response($json);
    }
    //add categorie
    #[Route('/addCategorie', name: 'app_categorieadd_json')]
    public function addCategorie(Request $request, SerializerInterface $serializerInterface, EntityManagerInterface $em)
    {
        /* $content = $request->getContent();
$jsonData = json_encode($content, true);*/
        $content = $request->getContent();
        list($name, $value) = explode('=', $content);

        $categorie = new Categorie();
        $categorie->setNom($value);

        $em->persist($categorie);
        $em->flush();

        return new Response('Categorie added successfully');
        /*
    /* $content=$request->getContent();
     error_log(json_encode($content));
    $jsonData = json_decode($request->getContent(), true);
    
     $data = $serializerInterface->deserialize(json_encode($jsonData), Categorie::class, 'json');
    $data=$serializerInterface->deserialize(json_encode($content),Categorie::class,'json');
     $em->persist($data);
    $em->flush();
    return new Response('Categorie add to suuccefully');
    */
    }
    //supprime json categorie

    #[Route('/deleteCategorie/{id}', name: 'app_categoriedelete_json')]
    public function deleteCategorie(Request $request, NormalizerInterface $normalizerInterface, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository(Categorie::class)->find($id);
        $em->remove($categorie);
        $em->flush();
        $jsonContent = $normalizerInterface->normalize($categorie, 'json', ['groups' => 'categories']);
        return new Response('Categorie deleted to suuccefully' . json_encode($jsonContent));
    }
    ///modification json categorie
    #[Route('/updateCategorie/{id}', name: 'app_categorieupdate_json')]
    public function updateCategorie(Request $request, NormalizerInterface $normalizerInterface, $id)
    {

        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true); // convert JSON string to associative array
        $nom = $data['nom']; // access the 'nom' property

        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository(Categorie::class)->find($id);
        var_dump($request->getContent());
        $categorie->setNom($data['nom']);

        $em->flush();
        $jsonContent = $normalizerInterface->normalize($categorie, 'json', ['groups' => 'categories']);
        return new Response('Categorie update to suuccefully' . json_encode($jsonContent));
    }
}
