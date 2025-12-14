<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Job;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 *
 * @method Job|null find($id, $lockMode = null, $lockVersion = null)
 * @method Job|null findOneBy(array $criteria, array $orderBy = null)
 * @method Job[]    findAll()
 * @method Job[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    // Optional helper method you can use later for employer-specific queries:
    // /**
    //  * @return Job[]
    //  */
    // public function findByCreatedByUser(User $user): array
    // {
    //     return $this->createQueryBuilder('j')
    //         ->andWhere('j.createdBy = :user')   // adapt field name if different
    //         ->setParameter('user', $user)
    //         ->orderBy('j.createdAt', 'DESC')
    //         ->getQuery()
    //         ->getResult();
    // }
}
