<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProduitRepository;
use App\Entity\Produit;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session,ProduitRepository $produitRepository): Response
    {

        $panier = $session->get("panier", []);
        $contenuPanier = [];
        $total=0;

        foreach($panier as $pan => $quantite){
            $produit = $produitRepository->find($pan);
            $contenuPanier[] = [
                "produit" => $produit,
                "quantite" => $quantite
         
            ];
        }
        foreach($contenuPanier as $element){

            $totalItem=$element['produit']->getPrix() * $element['quantite'];

            $total+= $totalItem;

        }
        return $this->render('panier/index.html.twig', [
            'elements' => $contenuPanier,
            'total' => $total,
           
        ]);
    }


    //ajouter au panier

    #[Route('/ajouterPanier/{id}', name: 'app_panier_ajouter')]
    

    public function add(Produit $produit, SessionInterface $session)
    {

        $panier = $session->get("panier", []);

        $id = $produit->getId();

        if(!empty($panier[$id])){
            $panier[$id]++;
        }else{
            $panier[$id] = 1;
        }

        $session->set("panier", $panier);

        return $this->redirectToRoute("app_panier");
    }

    //supprime fil quantite button -

#[Route('/remove/{id}', name: 'app_panier_supprimeQuantite')]
   
public function supprimeQuantite(Produit $produit, SessionInterface $session)
{

    // On récupère le panier actuel
    $panier = $session->get("panier", []);
    $id = $produit->getId();
    if(!empty($panier[$id])){
        if($panier[$id] > 1){
            $panier[$id]--;
        }else{
            unset($panier[$id]);
        }
    }


    $session->set("panier", $panier);

    return $this->redirectToRoute("app_panier");
}



//supprime tous la ligne de tableau panier


#[Route('/supprimeParLigne/{id}', name: 'app_panier_supprime_par_ligne')]
    
public function supprimeParLigne(Produit $produit, SessionInterface $session)
{

    $panier = $session->get("panier", []);
    $id = $produit->getId();

    if(!empty($panier[$id])){
        unset($panier[$id]);
    }
    $session->set("panier", $panier);

    return $this->redirectToRoute("app_panier");
}

//supprime tous les tableau du panien

#[Route('/supprimer', name: 'app_panier_supp_tous')]
    
public function supprimeTousLesPaniers(SessionInterface $session)
{
    $session->remove("panier");

    return $this->redirectToRoute("app_panier");

}


}

