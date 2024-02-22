<?php

namespace App\Repository\Platform\Lazada;

use App\Entity\Platform\Lazada\LazadaCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LazadaCategory>
 *
 * @method LazadaCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method LazadaCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method LazadaCategory[]    findAll()
 * @method LazadaCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LazadaCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LazadaCategory::class);
    }

    public function save(LazadaCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LazadaCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function listByRef()
    {
        return $this->createQueryBuilder('c', 'c.ref')->getQuery()->getResult();
    }

    public function search(string $name, int $limit = 50)
    {
        return $this->createQueryBuilder('c')
            ->select('c.name, c.path, c.ref as value')
            ->where('c.name LIKE :name')
            ->andWhere('c.leaf = TRUE')
            ->setParameter('name', "%$name%")
            ->setMaxResults($limit)
            ->getQuery()
            ->useQueryCache(true)
            ->getResult();
    }


//    /**
//     * @return LazadaCategory[] Returns an array of LazadaCategory objects
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

//    public function findOneBySomeField($value): ?LazadaCategory
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
