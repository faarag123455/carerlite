<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    // Add custom query helpers here if/when you need them, e.g.:
    // /**
    //  * @return Company[]
    //  */
    // public function findRecent(int $limit = 20): array
    // {
    //     return $this->createQueryBuilder('c')
    //         ->orderBy('c.createdAt', 'DESC')
    //         ->setMaxResults($limit)
    //         ->getQuery()
    //         ->getResult();
    // }
}
