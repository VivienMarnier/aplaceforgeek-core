<?php


namespace App\Repository;


use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    protected function paginate(QueryBuilder $qb, $limit = 10, $offset = 0)
    {
//        if (0 == $limit || 0 == $offset) {
//            throw new \LogicException('$limit & $offset must be greater than 0.');
//        }

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $currentPage = ceil(($offset + 1) / $limit);
        $pager->setMaxPerPage((int) $limit);
        $pager->setCurrentPage($currentPage);

        return $pager;
    }
}