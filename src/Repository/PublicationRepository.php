<?php

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Publication|null find($id, $lockMode = null, $lockVersion = null)
 * @method Publication|null findOneBy(array $criteria, array $orderBy = null)
 * @method Publication[]    findAll()
 * @method Publication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    // /**
    //  * @return Publication[] Returns an array of Publication objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Publication
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getUserFeed(Collection $games){

        $query = $this->getEntityManager()->createQuery(
            'SELECT p, g
             FROM App\Entity\Publication p
             INNER JOIN p.game = g
             WHERE p.game IN (:games)
             ORDER BY p.date DESC'
        )->setParameter('games', $games);

        return $query->getArrayResult();

        $qb = $this->createQueryBuilder('p');
        $qb->select('p','g');
        $qb->leftJoin('App\Entity\Game','g','p.game = g')
        ->where('p.game IN (:games)')
        ->addOrderBy('p.date','DESC')
        ->setParameter('games', $games);

        return $qb->getQuery()->getArrayResult();
    }
}
