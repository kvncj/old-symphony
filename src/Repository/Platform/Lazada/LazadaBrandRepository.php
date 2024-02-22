<?php

namespace App\Repository\Platform\Lazada;

use App\Entity\Platform\Lazada\LazadaBrand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LazadaBrand>
 *
 * @method LazadaBrand|null find($id, $lockMode = null, $lockVersion = null)
 * @method LazadaBrand|null findOneBy(array $criteria, array $orderBy = null)
 * @method LazadaBrand[]    findAll()
 * @method LazadaBrand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LazadaBrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LazadaBrand::class);
    }

    public function save(LazadaBrand $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LazadaBrand $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search(string $name, int $limit = 20)
    {
        return $this->createQueryBuilder('b')
            ->select('b.name, b.ref as value')
            ->where('b.name LIKE :name')
            ->setParameter('name', "$name%")
            ->setMaxResults($limit)
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }

//    /**
//     * @return LazadaBrand[] Returns an array of LazadaBrand objects
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

//    public function findOneBySomeField($value): ?LazadaBrand
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
