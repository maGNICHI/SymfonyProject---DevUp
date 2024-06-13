<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;


class CategorieController extends AbstractController
{

    private $paginator;
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }
   //affiche liste categorie par reposeterie

   #[Route('/categorie', name: 'app_categorie_list')]
   public function index(CategorieRepository $categorieRepository, Request $request): Response
   {
       $page = $request->query->getInt('page',1);
       $limit = 4;
       $paginator = $this->paginator;
       $categories = $paginator->paginate($categorieRepository->findAll(), $page, $limit);
        
       return $this->render('categorie/index.html.twig', array('listecategorie'=>$categories));

   }

   //ajouter categorie 
    #[Route('/ajouter', name: 'app_categorie_ajouter')]
    public function AjouterCategorie(Request $request, CategorieRepository $categorieRepository,ManagerRegistry $mg,FlashyNotifier $flashy): Response
    {
       
        $categorie = new Categorie();
    $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
 
       
 if ($form->isSubmitted() && $form->isValid()) {
        
           $entityManager=$mg->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();
            $flashy->success('Catégorie ajoutée avec succès.');
            return $this->redirectToRoute('app_categorie_list');
        }

        return $this->renderForm('categorie/ajouter.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }
//modiefier categorie 
    #[Route('/{id}/modifier', name: 'app_categorie_modifier')]
    public function modifier(Request $request, Categorie $categorie,ManagerRegistry $mg): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager=$mg->getManager();
            $entityManager->flush();
 
            return $this->redirectToRoute('app_categorie_list');
        }
 
        return $this->renderForm('categorie/modifier.html.twig', [
            'modifiercategorie' => $categorie,
            'form' => $form,
        ]);
    }
//supprime categorie 
    #[Route('/removecategorie/{id}', name: 'app_categorie_remove')]
    public function remove(ManagerRegistry $mg ,CategorieRepository $categorieRepository,$id): Response
    {
        $categorie= $categorieRepository->find($id);
        $sem=$mg->getManager();
      $sem-> remove($categorie);
        $sem->flush();
        
        return $this->redirectToRoute('app_categorie_list');
    }
}
