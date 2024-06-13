<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Entity\Produit;

use App\Entity\Rate;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\RateType;
class ProduittemplateController extends AbstractController

{
    private $paginator;
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }
    //affiche liste de produit de manière paginée.
    #[Route('/produittemplate', name: 'app_produittemplate')]
    public function index(ProduitRepository $produitRepository, Request $request): Response
    {   
       $page = $request->query->getInt('page', 1);
       $limit = 6;
       $paginator = $this->paginator;
       $produits = $paginator->paginate($produitRepository->findAll(), $page, $limit);

        return $this->render('produittemplate/index.html.twig', array('produits'=>$produits));
    }


     //detail liste de produit 
     #[Route('/produit/{id}', name: 'app_produit_details')]
     public function indexdetailproduit(Produit $produit,Request $request,ManagerRegistry $mg): Response
     {
        $rate= new Rate();
        $form=$this->createForm(RateType::class,$rate);
        $form->handleRequest($request);
        if ($form->isSubmitted()&& $form->isValid()){
            $rate->setProduit($produit);
            $entityManager=$mg->getManager();
            $entityManager->persist($rate);
            $entityManager->flush();
        }

         return $this->render('produittemplate/detail.html.twig', array('produitdetail'=>$produit, 'form' => $form->createView(),
        ));
     }

///fiter par min et max prix
#[Route('/rechercheParPrix', name: 'app_recherche_prix')]
public function RechercherPrix(Request $request, ProduitRepository $produitRepository): Response
{
    $minPrix=$request->get('min');
    $maxPrix=$request->get('max');
        $page = $request->query->getInt('page', 1);
        $limit = 6;
        $paginator = $this->paginator;
        $produits = $paginator->paginate($produitRepository->findByPrix($minPrix,$maxPrix), $page, $limit);
    return $this->render('produittemplate/index.html.twig',[
        'produits' =>$produits
        
        
        
    ]);
}



}
