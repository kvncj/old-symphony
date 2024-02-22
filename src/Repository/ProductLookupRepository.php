<?php

namespace App\Repository;

use App\Entity\ProductLookup;
use App\Entity\Team;
use App\Model\Product\Enum\ProductStatus;
use App\Model\Product\ProductLookupQuery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductLookup>
 *
 * @method ProductLookup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductLookup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductLookup[]    findAll()
 * @method ProductLookup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductLookupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductLookup::class);
    }

    public function save(ProductLookup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductLookup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search(ProductLookupQuery $params)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = " FROM product_lookup p";

        $teamIds = array_map(fn (Team $team) => $team->getId(), $params->getTeams());

        $sql .= sprintf(" WHERE p.team_id IN (%s)", implode(',', $teamIds));
        //$sql .= sprintf(" INNER JOIN p.team t WITH t.id IN (%s)", implode(',', $teamIds));

        if ($params->getStatus() !== ProductStatus::ALL->value) {
            $sql .= sprintf(" AND p.status = '%s'", $params->getStatus());
        }

        $search = $params->getSearch();
        if (null !== $search)
            $sql .= sprintf(" AND (p.name LIKE '%s' OR p.sku LIKE '%s')", "%$search%", "%$search%");

        $sql .= sprintf(" ORDER BY p.%s %s", $params->getOrderBy(), $params->getSequence());

        $countStmt = $conn->prepare("SELECT COUNT(*)" . $sql);
        $countResult = $countStmt->executeQuery();

        $sql .= sprintf(" LIMIT %d OFFSET %d;", $params->getPageSize(), $params->getOffset());
        $stmt = $conn->prepare("SELECT *" . $sql);
        $resultSet = $stmt->executeQuery();
        // returns an array of arrays (i.e. a raw data set)
        return [
            'total' => $countResult->fetchOne(),
            'products' => $resultSet->fetchAllAssociative()
        ];
    }

    //    /**
    //     * @return ProductLookup[] Returns an array of ProductLookup objects
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

    //    public function findOneBySomeField($value): ?ProductLookup
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
