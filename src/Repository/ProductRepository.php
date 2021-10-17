<?php

namespace App\Repository;

use App\Classe\Filter;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Requête de récupération des produits filtrés
     * @param Filter $search
     * @return int|mixed|string
     */
    public function findWithSearch(Filter $search)
    {
        $query = $this
            ->createQueryBuilder('p') // Sélection des catégories et de produits
            ->select('c','p')
            ->join('p.category', 'c');

        if (!empty($search->categories)){ // Sélectionner les produits qui contiennent les catégories cochées dans la recherche
            $query = $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);
        }

        if (!empty($search->string)){ // Sélectionner les produits qui correspondent au nom rentré
            $query = $query
                ->andWhere('p.name LIKE :string')
                ->setParameter('string', "%$search->string%"); //  Les % permettent de faire un recherche partielle
        }
        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
