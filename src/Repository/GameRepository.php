<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * @param $term
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return \Pagerfanta\Pagerfanta
     */
    public function search($term, $order = 'asc', $limit = 1, $offset = 1)
    {
        $qb = $this
            ->createQueryBuilder('g')
            ->select('g')
            ->orderBy('g.id', $order);

        if ($term) {
            $qb
                ->where('g.label LIKE ?1')
                ->setParameter(1, '%'.$term.'%');
        }

        return $this->paginate($qb, $limit, $offset);
    }

    // /**
    //  * @return Game[] Returns an array of Game objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Game
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param int $userId
     * @return array|int|string
     */
    public function getUserSubscriptions(int $userId){
        //$this->getEntityManager()->getConnection()->query()
    }
}
