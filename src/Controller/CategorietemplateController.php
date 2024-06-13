<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Entity\Categorie;

use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

class CategorietemplateController extends AbstractController
{
    private $paginator;
        public function __construct(PaginatorInterface $paginator)
        {
            $this->paginator = $paginator;
        }
    //affiche liste de categorie
    #[Route('/categorietemplate', name: 'app_categorietemplate')]
    public function index(CategorieRepository $categorieRepository, Request $request): Response
    {
       $page = $request->query->getInt('page', 1);
       $limit = 6;
       $paginator = $this->paginator;
       $categories = $paginator->paginate($categorieRepository->findAll(), $page, $limit);

        return $this->render('categorietemplate/index.html.twig',array('categories'=>$categories)
    );
    }


//affiche produit selon leur categorie
#[Route('/catedorie/{id}', name: 'app_produit_par_categorie')]
public function produitParCategorie(ProduitRepository $produitRepository,Categorie $categorie,Request $request): Response
{ 
        $page = $request->query->getInt('page', 1);
        $limit = 6;
        $paginator = $this->paginator;
        //affiche liste de produit
        $produits = $paginator->paginate($produitRepository ->afficheProduitParCategorie($categorie), $page, $limit);

    return $this->render('produittemplate/index.html.twig',array('produits'=>$produits)
);
}


//recherche par nom de categorie
#[Route('/rechercheParNom', name: 'app_recherche_par_nom_du_categorie')]
public function recgercheParNomDuCategorie(CategorieRepository $categorieRespositry , Request $request)
{
        $page = $request->query->getInt('page', 1);
        $limit = 6;
        $paginator = $this->paginator;
        $nom=$request->get('nom');

        $categories = $paginator->paginate($categorieRespositry->rechercheParNomDeCategorie($nom), $page, $limit);

   return $this->render('categorietemplate/index.html.twig', array('categories'=>$categories));

}


}
