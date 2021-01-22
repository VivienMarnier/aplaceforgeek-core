<?php


namespace App\Service;


use App\Entity\Game;
use App\Entity\Publication;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PublicationService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * @param Publication $publication
     * @param Game $game
     * @param User $author
     * @return Publication
     */
    public function createPublication(Publication $publication, Game $game, User $author){
        try{
            $publication->setDate(new \DateTime());
            $publication->setGame($game);
            $publication->setAuthor($author);
            $this->entityManager->persist($publication);
            $this->entityManager->flush();
            return $publication;
        }catch (\Exception $e){
            throw new \DomainException("Unable to save new game at this time.");
        }
    }
}