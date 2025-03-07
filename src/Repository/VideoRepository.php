<?php
//lié à la BD
namespace App\Repository;

use App\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Video>
 *
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findAll()
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function save(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Video $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search($criteria): array
    {
        $qb = $this->createQueryBuilder('v');
        
        // Ensure that category is always 'bjj'
        $qb->where('v.category = :category')
        ->setParameter('category', 'bjj');
        
        if (!empty($criteria['basePosition'])) {
            $qb->andWhere('v.basePosition = :basePosition')
            ->setParameter('basePosition', $criteria['basePosition']);

            if (!empty($criteria['endingPosition'])) {
                $qb->andWhere('v.endingPosition = :endingPosition')
                ->setParameter('endingPosition', $criteria['endingPosition']);
            }
        } elseif (!empty($criteria['endingPosition'])) {
            $qb->andWhere('v.endingPosition = :endingPosition')
            ->setParameter('endingPosition', $criteria['endingPosition']);
        }

        $query = $qb->getQuery();

        return $query->execute();
    }

    public function findOtherVideo(): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.category != :category')
            ->setParameter('category', 'bjj');

        $query = $qb->getQuery();

        return $query->execute();
    }

//    /**
//     * @return Video[] Returns an array of Video objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Video
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
