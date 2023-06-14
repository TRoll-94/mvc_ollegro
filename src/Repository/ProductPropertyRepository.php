<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\ProductProperty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;

/**
 * @extends ServiceEntityRepository<ProductProperty>
 *
 * @method ProductProperty|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductProperty|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductProperty[]    findAll()
 * @method ProductProperty[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductPropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductProperty::class);
    }

    public function save(ProductProperty $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductProperty $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getUniqueProductProperties(Category $id, array $properties=null): Collection
    {
        if ($properties==null) {
           return $id->getProductProperties();
        }
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();

        $qb->select('pp')
            ->from(ProductProperty::class, 'pp')
            ->where('pp.category = :id')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->in('pp.id', ':properties'),
                $qb->expr()->notIn('pp.code', ':codes')
            ))
            ->setParameter('id', $id)
            ->setParameter('properties', $properties)
            ->setParameter('codes', array_unique(array_column($properties, 'code')));

        $query = $qb->getQuery();
        return $query->getResult();
    }

    public function getProductCount(int $id): int
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();

        $qb ->from('App\Entity\ProductProperty', 'pp')
            ->leftJoin('pp.products', 'products')
            ->groupBy('pp.id')
            ->where('pp.id = :id')
            ->setParameter('id', $id)
            ->select('COUNT(products.id)');

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException|NoResultException $e) {
            return -1;
        }
    }

//    /**
//     * @return ProductProperty[] Returns an array of ProductProperty objects
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

//    public function findOneBySomeField($value): ?ProductProperty
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
