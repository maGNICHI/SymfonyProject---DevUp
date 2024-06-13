<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Form\ProduitType;

class ProduitGamerController extends AbstractController
{
    
    private $paginator;
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }
    
    //affiche produit gamer
    #[Route('/produitGamer', name: 'app_list_produit_gamer')]
    public function indexProduitGamer(ProduitRepository $produitRepository, Request $request): Response
    {
        $user = $this->getUser();
       $page = $request->query->getInt('page', 1);
       $limit = 6;
       $paginator = $this->paginator;
       $Produits = $paginator->paginate($produitRepository->afficherProduitParGamer($user->getId()), $page, $limit);
        
        return $this->render('produit_gamer/index.html.twig', array('listeproduit'=>$Produits)
        );
    }

    //recherche par nom de produit

#[Route('/recherProduitGamerParNom', name: 'app_recherche_produit_gamer_par_nom')]
public function rechercheParNomDuProduitGmer(ProduitRepository $produitRespositry , Request $request)
{
  $page = $request->query->getInt('page', 1);
  $limit = 1;
  $paginator = $this->paginator;


   $nom=$request->get('nom');

   $produit=  $paginator->paginate($produitRespositry->rechercheParNomDeProduit($nom), $page, $limit);
 
   return $this->render('produit_gamer/index.html.twig', array('listeproduit'=>$produit));

}

//ajout produit
#[Route('/ajouterProduitGamer', name: 'app_produit_gamer_ajouter')]
public function AjouterProduit(Request $request, ProduitRepository $produitRepository,ManagerRegistry $mg,SluggerInterface $slugger): Response
{
     $produit = new Produit();
    $produit = new Produit();
    $user = $this->getUser();
     $produit->setUser($user);
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      
        //ajout image 
        $fileName = $form->get('image')->getData();
        if ($fileName) {
            $originalFilename = pathinfo($fileName->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$fileName->guessExtension();
            $newFilename = $originalFilename;
                $fileName->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
          
            $produit->setImage($newFilename);
        //end ajou image
        $entityManager=$mg->getManager();
        $entityManager->persist($produit);
        $entityManager->flush();
        return $this->redirectToRoute('app_list_produit_gamer');
   }
} 
    return $this->renderForm('produit_gamer/ajouter.html.twig', [
        'produit' => $produit,
        'form' => $form,
    ]);
}
 

//modifier produit
     
#[Route('/{id}/modifierProduitGamer', name: 'app_produit_gamer_modifier')]
public function modifier(Request $request, Produit $produit,ManagerRegistry $mg,SluggerInterface $slugger): Response
{

    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        //modifier image
        $fileName = $form->get('image')->getData();
        if ($fileName) {
            $originalFilename = pathinfo($fileName->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$fileName->guessExtension();
            $modifierFilename = $originalFilename;
          
                $fileName->move(
                    $this->getParameter('images_directory'),
                    $modifierFilename
                );
           
            $produit->setImage($modifierFilename);
        //end mofification
        $entityManager=$mg->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('app_list_produit_gamer');
    }}

    return $this->renderForm('produit_gamer/modifier.html.twig', [
        'produit' => $produit,
        'form' => $form,
    ]);
}


//supprime produit
#[Route('/removeproduitGamer/{id}', name: 'app_produit_gamer_remove')]
public function remove(ManagerRegistry $mg ,ProduitRepository $produitRepository,$id): Response
{
    $produit= $produitRepository->find($id);
    $sem=$mg->getManager();
  $sem-> remove($produit);
    $sem->flush();
    
    return $this->redirectToRoute('app_list_produit_gamer');
}


}
