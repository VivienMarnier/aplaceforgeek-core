<?php


namespace App\Controller;


use App\Entity\Game;
use App\Exception\ResourceValidationException;
use App\Repository\GameRepository;
use App\Service\FileService;
use App\Service\GameService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

class GameController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var FileService
     */
    protected $fileService;

    public function __construct(EntityManagerInterface $entityManager, FileService $fileService){
        $this->entityManager = $entityManager;
        $this->fileService = $fileService;
    }

    /**
     * @Rest\Post("/admin/games/create", name="create-game")
     * @ParamConverter("game", converter="fos_rest.request_body")
     * @param Game $game
     * @param ConstraintViolationList $violations
     * @return Response
     */
    public function create(Game $game, ConstraintViolationList $violations){
        if(count($violations)){
            throw new ResourceValidationException("The JSON sent contains invalid data.");
        }else{
            try{
                $game->setPicture($this->fileService->saveFileToFiler($game->getPicture(), FileService::GAME_REPOSITORY));
                $this->entityManager->persist($game);
                $this->entityManager->flush();
                $view = $this->view($game, 201);
            }catch(\Exception $e){
                $this->fileService->deleteFileToFiler($game->getPicture());
                throw new Exception("Unable to save new game at this time.");
            }
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Put(
     *     "/admin/games/{id}",
     *     name="edit-game",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("game", converter="fos_rest.request_body")
     * @param int $id
     * @param Game $game
     * @param ConstraintViolationList $violations
     * @param GameRepository $gameRepository
     * @return Response
     */
    public function edit(int $id, Game $game, ConstraintViolationList $violations, GameRepository $gameRepository){
        $dbGame = $gameRepository->find($id);
        if(!$dbGame){
            throw new EntityNotFoundException("No game found for id " . $id);
        }

        if(count($violations) > 0){
            throw new ResourceValidationException("The JSON sent contains invalid data.");
        }else{
            try{
                $dbGame->setDescription($game->getDescription());
                $dbGame->setLabel($game->getLabel());
                //HANDLE NEW PICTURE
                $previousPicture = $dbGame->getPicture();
                if(!empty($game->getPicture()) && $game->getPicture() !== ""){
                    $game->setPicture($this->fileService->saveFileToFiler($game->getPicture(), FileService::GAME_REPOSITORY));
                    $this->fileService->deleteFileToFiler($previousPicture);
                }else{
                    $game->setPicture($previousPicture);
                }

                $this->entityManager->persist($game);
                $this->entityManager->flush();
                $game->setPicture($this->fileService->getBase64FileDatas($game->getPicture()));
                $view = $this->view($game, 200);
            }catch(\Exception $e){
                throw new \Exception("Unable to edit game with id " . $game->getId() . " at this time.");
            }
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Patch(
     *     "/admin/games/{id}",
     *      name="active_game",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("game", converter="fos_rest.request_body")
     * @param GameRepository $gameRepository
     * @return Response
     */
    public function active(int $id, Game $game, GameRepository $gameRepository){
        $dbGame = $gameRepository->find($id);
        if(!$dbGame){
            throw new EntityNotFoundException("No user found for id " . $id);
        }

        try{
            $dbGame->setActive($game->getActive());
            $this->entityManager->flush();
            $view = $this->view($dbGame->getId(), 200);
        }catch (\Exception $e){
            throw new \Exception("Unable to edit game with id " . $dbGame->getId() . "at this time.");
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Delete(
     *     "/admin/games/{id}",
     *     name="delete-game",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\QueryParam(
     *     name="id",
     *
     * )
     * @param int $id
     * @param GameRepository $gameRepository
     * @return Response
     */
    public function delete(int $id, GameRepository $gameRepository){
        $game = $gameRepository->find($id);
        if(!$game){
            throw new EntityNotFoundException("No game found for id " . $id);
        }
        try {
            $this->fileService->deleteFileToFiler($game->getPicture());
            $this->entityManager->remove($game);
            $this->entityManager->flush();
            $view = $this->view($id, 200);
        }catch(\Exception $e){
            throw new \Exception("Unable to delete game " . $id . " at this time.");
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Get("/games", name="games-list")
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]+",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="10",
     *     description="Max number of games per page."
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="1",
     *     description="The pagination offset"
     * )
     * @param GameService $gameService
     * @return Response
     */
    public function list(ParamFetcherInterface $paramFetcher,GameService $gameService){
        try{
            $options['keyword'] = $paramFetcher->get('keyword');
            $options['order'] = $paramFetcher->get('order');
            $options['limit'] = $paramFetcher->get('limit');
            $options['offset'] = $paramFetcher->get('offset');
            $result = $gameService->searchGames($options);
            $view = $this->view($result, 200);
        }catch(\Exception $e){
            throw new \Exception("Unable to retrieve games list at this time.");
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Get(
     *     "/games/{id}",
     *     name="get-game",
     *     requirements = {"id"="\d+"}
     * )
     * @param int $id
     * @param GameRepository $gameRepository
     * @return Response
     */
    public function getGame(int $id, GameRepository $gameRepository){
        $game = $gameRepository->find($id);
        if(!$game){
            throw new EntityNotFoundException("No game found for id " . $id);
        }
        $view = $this->view($game, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Put("/games/subscribe/{id}", name="subscribe_game")
     * @param int $id
     * @param GameRepository $gameRepository
     * @return Response
     */
    public function subscribe(int $id, GameRepository $gameRepository){
        $user = $this->getUser();
        $dbGame = $gameRepository->find($id);
        if(!$dbGame){
            throw new EntityNotFoundException("No game found for id " . $id);
        }

        try{
            $dbGame->addSubscriber($user);
            $this->entityManager->flush();
            $view = $this->view($dbGame, 200);
        }catch (\Exception $e){
            throw new \Exception("Unable for user to subscribe to game " . $dbGame->getId() . " at this time.");
        }

        return $this->handleView($view);
    }


}