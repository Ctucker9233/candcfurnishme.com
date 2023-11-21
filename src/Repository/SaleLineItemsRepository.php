<?php

namespace App\Repository;

use App\Entity\SaleLineItems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SaleLineItems>
 *
 * @method SaleLineItems|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaleLineItems|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaleLineItems[]    findAll()
 * @method SaleLineItems[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleLineItemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SaleLineItems::class);
    }

    public function save(SaleLineItems $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SaleLineItems $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return SaleLineItems[] Returns an array of SaleLineItems objects
     */
    public function findBySaleId($value): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.sale = :val')
            ->setParameter('val', $value)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPriceByItem($value): ?SaleLineItems
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.item = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
