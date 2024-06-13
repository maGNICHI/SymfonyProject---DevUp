<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;


class ProduitController extends AbstractController

{
    private $paginator;
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    //affiche liste produit par reposeterie
    #[Route('/produit', name: 'app_produit_list')]
    public function index(ProduitRepository $produitRepository, Request $request): Response
    {
       $page = $request->query->getInt('page', 1);
       $limit = 6;
       $paginator = $this->paginator;
       $Produits = $paginator->paginate($produitRepository->findAll(), $page, $limit);

        
        return $this->render('produit/index.html.twig', array('listeproduit'=>$Produits)
        );
    }


#[Route('/ajouterProduit', name: 'app_produit_ajouter')]
public function AjouterProduit(Request $request, ProduitRepository $produitRepository,ManagerRegistry $mg,SluggerInterface $slugger): Response
{
     $produit = new Produit();


    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);
   
    if ($form->isSubmitted() && $form->isValid()) {

      
        //ajout image

        $fileName = $form->get('image')->getData();
        if ($fileName) {
            $originalFilename = pathinfo($fileName->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$fileName->guessExtension();

                $fileName->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
          
            $produit->setImage($newFilename);
        //end ajou image

        $entityManager=$mg->getManager();
        $entityManager->persist($produit);
        $entityManager->flush();
        return $this->redirectToRoute('app_produit_list');
   }

} 

    return $this->renderForm('produit/ajouter.html.twig', [
        'produit' => $produit,
        'form' => $form,
    ]);
}
 

     
#[Route('/{id}/modifierProduit', name: 'app_produit_modifier')]
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
          
                $fileName->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
           
            $produit->setImage($newFilename);

        //end mofification

        $entityManager=$mg->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('app_produit_list');
    }}

    return $this->renderForm('produit/modifier.html.twig', [
        'produit' => $produit,
        'form' => $form,
    ]);
}

#[Route('/removeproduit/{id}', name: 'app_produit_remove')]
public function remove(ManagerRegistry $mg ,ProduitRepository $produitRepository,$id): Response
{//bch find par id mel entite
    $produit= $produitRepository->find($id);
    $sem=$mg->getManager();
  $sem-> remove($produit);
    $sem->flush();
    
    return $this->redirectToRoute('app_produit_list');
}

//recherche par nom de produit

#[Route('/rechercheDashbordParNom', name: 'app_recherche_dashboard_par_nom_du_produit')]
public function recgercheParNomDuProduit(ProduitRepository $produitRespositry , Request $request)
{
  $page = $request->query->getInt('page', 1);
  $limit = 6;
  $paginator = $this->paginator;


   $nom=$request->get('nom');

   $produit=  $paginator->paginate($produitRespositry->rechercheParNomDeProduit($nom), $page, $limit);
 
   return $this->render('produit/index.html.twig', array('listeproduit'=>$produit));

}

}
