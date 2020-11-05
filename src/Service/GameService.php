<?php


namespace App\Service;


use App\Repository\GameRepository;
use App\Entity\Game;

class GameService
{
    /**
     * @var GameRepository
     */
    protected $gameRepository;

    /**
     * @var FileService
     */
    protected $fileService;


    public function __construct(GameRepository $gameRepository, FileService $fileService){
        $this->gameRepository = $gameRepository;
        $this->fileService = $fileService;
    }

    /**
     * Gets Games list with picture base64 datas
     * @return Game[]
     */
    public function getAllGamesWithPictureDatas(){
        $games = $this->gameRepository->findAll();
        foreach ($games as $game){
            $game->setPicture($this->fileService->getBase64FileDatas($game->getPicture()));
        }
        return $games;
    }

    /**
     * Get a Games paginator from specific research
     * @param array $options
     * @return iterable|null
     */
    public function searchGames(Array $options){
        $paginator = $this->gameRepository->search($options['keyword'],$options['order'],$options['limit'],$options['offset']);
        $games = $paginator->getCurrentPageResults();
        foreach ($games as $game){
            $game->setPicture($this->fileService->getBase64FileDatas($game->getPicture()));
        }
        $result['totalItems'] = $paginator->getNbResults();
        $result['games'] = $games;
        return $result;
    }
}