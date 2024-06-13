<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RateRepository;
use Knp\Component\Pager\PaginatorInterface;

class RateController extends AbstractController
{
    private $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }
    #[Route('/rate', name: 'app_rate')]
    public function index(RateRepository $rateRepository, Request $request): Response
    {
         // Set the current page number (default is 1)
       $page = $request->query->getInt('page', 1);
       // Set the number of categories to display per page
       $limit = 3;
       // Get the paginated category list
       $paginator = $this->paginator;
       $rate = $paginator->paginate($rateRepository->findAll(), $page, $limit);
        return $this->render('rate/index.html.twig', [
            'rate'=>$rate,
        ]);
    }
}
