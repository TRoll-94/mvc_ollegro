<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;

/**
 * @extends ServiceEntityRepository<Product>
 *
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

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function withSameSkuAndProperties($sku, $properties): array
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();

        $qb->select('p')
            ->from('App\Entity\Product', 'p')
            ->leftJoin('p.properties', 'pp')
            ->where($qb->expr()->eq('p.sku', ':sku'))
            ->andWhere($qb->expr()->in('pp.id', ':properties'))
            ->setParameter('sku', $sku)
            ->setParameter('properties', $properties);

        return $qb->getQuery()->getResult();
    }

    public function findByUser(User $user): array
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();

        $qb ->select('p')
            ->from('App\Entity\Product', 'p')
            ->where('p.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('p.sku', 'ASC')
            ->addOrderBy('p.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function isProductOwner(Product $product, User $user): bool {
        return $user === $product->getOwner();
    }

    public function productsWithTheSameSku(Product $product): array
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();

        $qb->select('p')
            ->from('App\Entity\Product', 'p')
            ->leftJoin('p.properties', 'pp')
            ->where($qb->expr()->eq('p.sku', ':sku'))
            ->andWhere($qb->expr()->neq('p.id', ':id'))
            ->setParameter('sku', $product->getSku())
            ->setParameter('id', $product->getId());

        return $qb->getQuery()->getResult();
    }

    public function countPurchases(Product $product)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb -> select('Count(c.id)')
            -> from('App\Entity\Cart', 'c')
            ->where('c.product = :product')
            ->setParameter('product', $product);

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
            return 1;
        }
    }

//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
