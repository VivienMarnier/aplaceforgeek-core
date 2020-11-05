<?php


namespace App\Service;


use App\Repository\GameRepository;
use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
use \App\Entity\User;

class UserService
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var GameRepository
     */
    protected $gameRepository;

    protected $publicationRepository;

    public function __construct(UserRepository $userRepository, GameRepository $gameRepository, PublicationRepository $publicationRepository)
    {
        $this->userRepository = $userRepository;
        $this->gameRepository = $gameRepository;
        $this->publicationRepository = $publicationRepository;
    }

    /**
     * @return User[]
     */
    public function getPaginatorList(){
        return $this->userRepository->findAll();
    }

    /**
     * Retrieves the list of games to which the user has subscribed
     * @param int $userId
     * @return mixed
     */
    public function getUserSubscriptions(int $userId){
        return $this->gameRepository->getUserSubscriptions($userId);
    }

    public function getUserFeed(int $userId){
        return $this->publicationRepository->getUserFeed($userId);
    }

}