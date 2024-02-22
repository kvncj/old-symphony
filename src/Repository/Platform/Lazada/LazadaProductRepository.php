<?php

namespace App\Repository\Platform\Lazada;

use App\Entity\Platform\Lazada\LazadaProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LazadaProduct>
 *
 * @method LazadaProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method LazadaProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method LazadaProduct[]    findAll()
 * @method LazadaProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LazadaProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LazadaProduct::class);
    }

    public function save(LazadaProduct $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LazadaProduct $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LazadaProduct[] Returns an array of LazadaProduct objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LazadaProduct
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
