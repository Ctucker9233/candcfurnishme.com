<?php

namespace App\Repository;

use App\Entity\ItemLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemLocation>
 *
 * @method ItemLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemLocation[]    findAll()
 * @method ItemLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemLocation::class);
    }

    public function add(ItemLocation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ItemLocation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByItemId($value): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.itemId = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByLocItemId($value): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.locItemId = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(1000)
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return ItemLocation[] Returns an array of ItemLocation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ItemLocation
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
