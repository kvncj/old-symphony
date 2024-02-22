<?php

namespace App\Repository\Platform\Lazada;

use App\Entity\Platform\Lazada\LazadaStore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LazadaStore>
 *
 * @method LazadaStore|null find($id, $lockMode = null, $lockVersion = null)
 * @method LazadaStore|null findOneBy(array $criteria, array $orderBy = null)
 * @method LazadaStore[]    findAll()
 * @method LazadaStore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LazadaStoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LazadaStore::class);
    }

    public function save(LazadaStore $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LazadaStore $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LazadaStore[] Returns an array of LazadaStore objects
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

//    public function findOneBySomeField($value): ?LazadaStore
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
