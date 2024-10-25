<?php

namespace App\Repository;

use App\Entity\Lesson;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 *
 * @method Lesson|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lesson|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lesson[]    findAll()
 * @method Lesson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    public function remove(Lesson $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(Lesson $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findUpcomingLessons(): array
    {
        $today = new \DateTime('today midnight');
        $twoWeeksLater = (clone $today)->modify('+2 weeks');

        return $this->createQueryBuilder('l')
            ->andWhere('l.date >= :today')
            ->andWhere('l.date <= :twoWeeksLater')
            ->setParameter('today', $today)
            ->setParameter('twoWeeksLater', $twoWeeksLater)
            ->orderBy('l.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPastLessonsByUser(User $user): array
    {
        $today = new \DateTime('today midnight'); // Start of today

        return $this->createQueryBuilder('l')
            ->innerJoin('l.users', 'u')            // Join with the User entity
            ->andWhere('u.id = :userId')           // Filter by user ID
            ->andWhere('l.date < :today')          // Only past lessons
            ->setParameter('userId', $user->getId())
            ->setParameter('today', $today)
            ->orderBy('l.date', 'DESC')            // Order by date descending
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Lesson[] Returns an array of Lesson objects
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

//    public function findOneBySomeField($value): ?Lesson
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
