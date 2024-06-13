<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use SebastianBergmann\Environment\Console;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProduitJsonController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    #[Route('/produit/json', name: 'app_produit_json')]
    public function index(): Response
    {
        return $this->render('produit_json/index.html.twig', [
            'controller_name' => 'ProduitJsonController',
        ]);
    }


    ///affiche les categorie en fct de json 
    #[Route('/allProduit', name: 'app_produitall_json')]
    public function getProduit(ProduitRepository $produitRepository, SerializerInterface $serializerInterface): Response
    {
        $produits = $produitRepository->findAll();

        $categoriesArray = [];

        foreach ($produits as $produit) {

            $nomCategorie = $produit->getCategorie()->getNom();

            $categoriesArray[] = [
                'id' => $produit->getId(),
                'nomcategorie' => $nomCategorie,
                'nameproduit' => $produit->getNom(),
                'description' => $produit->getDescription(),
                'prix' => $produit->getPrix(),
                'TelContact' => $produit->getTelContact(),
                'Image' => $produit->getImage(),
                'Commentaire' => $produit->getCommentaire(),
                'quantite' => $produit->getQuantite(),


            ];
        }

        $json = $serializerInterface->serialize($categoriesArray, 'json', ['groups' => "produits"]);
        return  new Response($json);
    }

    //add categorie
    #[Route('/addProduit', name: 'app_categorieadd_json')]
    public function addProduit(Request $request, SerializerInterface $serializerInterface, EntityManagerInterface $em, CategorieRepository $categorieRepository)
    {


        /* $content = $request->getContent();
$jsonData = json_encode($content, true);*/

        /*
 /* $content=$request->getContent();
  
 
  $data = $serializerInterface->deserialize($content, Categorie::class, 'json');
 $data=$serializerInterface->deserialize(json_encode($content),Categorie::class,'json');
  $em->persist($data);
 $em->flush();
 return new Response('Categorie add to suuccefully');
 */
        //  $content = 'nom="fghjk", description="iuytrd", prix=123, telContact=26555574, image="Image_created_with_a_mobile_phone.png", commentaire="dcfjkl", quantite=2, idcategorie=4';
        ////content:bch ya9ra men requet les donne mata3in   a
        $content = $request->getContent();
        $mydata = array();
        parse_str($content, $mydata);
        $this->logger->info($content);



        // remove double quotes if they surround the values
        //$content = str_replace('"', '', $content);

        /// bch na7i les virgule mel le forma mata3 content bch raja3ha form json(cle et valeur)
        $pairs = explode(',', $content);
        //var_dump($pairs);
        //tableau fara8 7atit fih donne
        $data = [];

        // iterate over the key-value pairs and add them to the result array
        //boucle for lil pairs 
        foreach ($pairs as $pair) {

            list($key, $value) = explode('=', $pair);
            $data[trim($key)] = trim($value);
        }
        //var_dump($data);
        // $content = $request->getContent();
        //var_dump($content);
        // $data = json_decode($content, true);
        //var_dump($data);
        $nom = $mydata['nom'];
        $prix = $mydata['prix'];
        $quantite = $mydata['quantite'];
        $image = $mydata['image'];
        $description = $mydata['description'];
        $telContact = $mydata['telContact'];
        $commentaire = $mydata['commentaire'];
        $idcategorie = $mydata['idcategorie'];
        $produit = new Produit();
        $produit->setNom($nom);
        $produit->setDescription($description);
        $produit->setPrix($prix);
        $produit->setTelContact($telContact);
        $produit->setImage($image);
        $produit->setCommentaire($commentaire);
        $produit->setQuantite($quantite);
        ///bch nejbed men tableau categorie fil  base ely 3anda id ely 3adinaha fil paramtre,objet(ya3ni id w nom)
        $categorie = $categorieRepository->find($idcategorie);
        // Assign the category to the product
        //set lil objet 5ater kn bch n7etlo kn id mch bch yafhamny
        $produit->setCategorie($categorie);
        $em->persist($produit);
        $em->flush();

        return new Response('produit added successfully');
    }
    //supprime json categorie

    #[Route('/deleteProduit/{id}', name: 'app_produitdelete_json')]
    public function deleteProduit(Request $request, NormalizerInterface $normalizerInterface, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $produit = $em->getRepository(Produit::class)->find($id);
        $em->remove($produit);
        $em->flush();
        $jsonContent = $normalizerInterface->normalize($produit, 'json', ['groups' => 'categories']);
        return new Response('Produit deleted to suuccefully' . json_encode($jsonContent));
    }



    ///modification json categorie
    #[Route('/updateProduit/{id}', name: 'app_produitupdate_json')]
    public function updateProduit(Request $request, NormalizerInterface $normalizerInterface, $id)
    {

        /*  $em = $this ->getDoctrine()->getManager();
     $produit=$em->getRepository(Produit::class)->find($id);
     $produit->setNom($request->get('nom'));
     $produit->setDescription($request->get('description'));
     $produit->setPrix($request->get('prix'));
     $produit->setTelContact($request->get('tel_Contact'));
     $produit->setImage($request->get('image'));
     $produit->setCommentaire($request->get('commentaire'));
     $produit->setQunatite($request->get('quantite'));
    $em->flush();
    $jsonContent=$normalizerInterface->normalize($produit,'json',['groups'=>'produits']);
    return new Response('produit update to suuccefully'.json_encode($jsonContent));
    */
        $data = $request->getContent();
        //$data = json_decode($jsonData, true); // convert JSON string to associative array



        $em = $this->getDoctrine()->getManager();
        $produit = $em->getRepository(Produit::class)->find($id);
        $mydata = array();
        parse_str($data, $mydata);
        $this->logger->info($data);
        var_dump($request->getContent());
        $produit->setNom($mydata['nom']);
        $produit->setDescription($mydata['description']);
        $produit->setPrix($mydata['prix']);
        $produit->setTelContact($mydata['telContact']);
        $produit->setImage($mydata['image']);
        $produit->setCommentaire($mydata['commentaire']);
        $produit->setQuantite($mydata['quantite']);
        $em->flush();

        $jsonContent = $normalizerInterface->normalize($produit, 'json', ['groups' => 'produits']);
        return new Response("Produit updated successfully" . json_encode($jsonContent));
    }
}
