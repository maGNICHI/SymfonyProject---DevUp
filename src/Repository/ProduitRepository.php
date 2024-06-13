<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function save(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Produit[] Returns an array of Produit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


//affiche produit selon leur categorie
public function afficheProduitParCategorie($categorie){
    return $this->createQueryBuilder('p')
    ->andWhere('p.categorie = :prod')
    ->setParameter('prod', $categorie)
    ->getQuery()
    ->getResult();

}



  // filter par prix (min et max)
  public function findByPrix($minPrix,$maxPrix)
  {
  return $this->createQueryBuilder('p')
  ->andWhere('p.prix >= :minPrix')
  ->setParameter('minPrix', $minPrix)
  ->andWhere('p.prix <= :maxPrix')
  ->setParameter('maxPrix', $maxPrix)
  ->getQuery()
  ->getResult()
  ;
  }

  //methode affiche le produit tab3a gamer :produitgamerreposotery
public function afficherProduitParGamer($userId){
     return $this->createQueryBuilder('p') 
     ->andWhere('p.user = :user_id ')
     ->setParameter('user_id', $userId)
     ->getQuery()
     ->getResult();
}

//rechercher par nom de produit
public function rechercheParNomDeProduit($nomProduit)
{
    $qb = $this->createQueryBuilder('p')
        ->where('p.nom LIKE  :x')
        ->setParameter('x', $nomProduit);
    return $qb->getQuery()->getResult();
}



}
