<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findBySearchTerm($searchTerm = null, $limit = 10, $page = 1)
{
    $queryBuilder = $this->createQueryBuilder('c');

    if (!empty($searchTerm)) {
        $queryBuilder
            ->where('c.name LIKE :searchTerm OR c.suchnummer LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }

    $queryBuilder->orderBy('c.CreatedAt', 'DESC');
    
    return $queryBuilder->getQuery()->getResult();
}

    public function getPaginatedCustomers($limit = 10, $page = 1) {
    $queryBuilder = $this->createQueryBuilder('c')
        ->orderBy('c.CreatedAt', 'DESC');

    $query = $queryBuilder->getQuery();

    $paginator = new Paginator($query);

    $paginator->getQuery()
        ->setFirstResult($limit * ($page - 1))
        ->setMaxResults($limit);

    return $paginator;
    }

    public function findAllNonArchivedCustomers(): array
    {
    return $this->createQueryBuilder('c')
        ->andWhere('c.isArchived = :isArchived')
        ->setParameter('isArchived', false)
        ->getQuery()
        ->getResult();
    }

    public function findAllArchivedCustomers()
    {
    return $this->createQueryBuilder('c')
        ->where('c.isArchived = :archived')
        ->setParameter('archived', true)
        ->getQuery()
        ->getResult();
    }

}
