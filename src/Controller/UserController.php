<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserService
     */
    protected $userService;

    public function __construct(EntityManagerInterface $entityManager, UserService $userService){
        $this->entityManager = $entityManager;
        $this->userService = $userService;
    }

    /**
     * @Rest\Patch(
     *     "/admin/users/{id}",
     *      name="active_user",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param int $id
     * @param UserRepository $userRepository
     * @return Response
     */
    public function active(int $id, User $user, UserRepository $userRepository){
        $dbUser = $userRepository->find($id);
        if(!$dbUser){
            throw new EntityNotFoundException("No user found for id " . $id);
        }

        try{
            $dbUser->setActive($user->getActive());
            $this->entityManager->flush();
            $view = $this->view($dbUser->getId(), 200);
        }catch (\Exception $e){
            throw new \Exception("Unable to edit user with id " . $dbUser->getId() . "at this time.");
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Get("/admin/users", name="users_list")
     * @return Response
     */
    public function list(){
        try{
            $users = $this->userService->getPaginatorList();
            $view = $this->view($users, 200);
        }catch(\Exception $e){
            throw new \Exception("Unable to retrieve users list at this time.");
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Get(
     *     "/users/{id}/subscriptions",
     *     name="get-user-subscriptions",
     *     requirements = {"id"="\d+"}
     * )
     * @param int $id
     * @param UserRepository $userRepository
     * @param GameRepository $gameRepository
     * @return Response
     */
    public function getUserSubscriptions(int $id, UserRepository $userRepository){
        $dbUser = $userRepository->find($id);
        if(!$dbUser){
            throw new EntityNotFoundException("No user found for id " . $id);
        }

        $subscriptions = $dbUser->getSubscriptions();

        $view = $this->view($subscriptions, 200);

        return $this->handleView($view);
    }

}