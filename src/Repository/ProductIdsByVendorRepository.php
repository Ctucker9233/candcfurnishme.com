<?php

namespace App\Repository;

use App\Entity\ProductIdsByVendor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductIdsByVendor>
 *
 * @method ProductIdsByVendor|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductIdsByVendor|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductIdsByVendor[]    findAll()
 * @method ProductIdsByVendor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductIdsByVendorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductIdsByVendor::class);
    }

    public function save(ProductIdsByVendor $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductIdsByVendor $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ProductIdsByVendor[] Returns an array of ProductIdsByVendor objects
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

//    public function findOneBySomeField($value): ?ProductIdsByVendor
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
