<?php


namespace App\Controller;


use App\Entity\Game;
use App\Entity\Publication;
use App\Exception\ResourceValidationException;
use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
use App\Service\PublicationService;
use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class PublicationController extends AbstractController
{
    /**
     * @var PublicationService
     */
    protected $publicationService;

    public function __construct(PublicationService $publicationService){
        $this->publicationService = $publicationService;
    }

    /**
     * @Rest\Post(
     *     "/games/{id}/publications/create",
     *      name="create-game-publication",
     *     requirements = {"id"="\d+"}
     * )
     * @ParamConverter("publication", converter="fos_rest.request_body")
     * @param Publication $publication
     * @param Game $game
     * @param ConstraintViolationListInterface $violations
     * @return Response
     */
    public function create(Publication $publication, Game $game, ConstraintViolationListInterface $violations){
        $this->handleViolations($violations);

        try {
            $result = $this->publicationService->createPublication($publication,$game,$this->getUser());
            $view = $this->view($result, 200);
        }catch(\DomainException $e){

        }

        return $this->handleView($view);
    }

//    /**
//     * @Rest\Post("/publications/create", name="create-publication")
//     * @param Request $request
//     * @param GameRepository $gameRepository
//     * @return Response
//     */
//    public function create(Request $request, GameRepository $gameRepository){
//        try{
//            $options = [
//                'method' => 'POST',
//            ];
//
//            $datas = json_decode($request->getContent(), true);
//            $publication = new Publication();
//            $publication->setDate(new \DateTime());
//            $form = $this->createForm(PublicationType::class,$publication,$options);
//            $form->submit($datas);
//            $publication->setAuthor($this->getUser());
//
//            if($form->isValid()){
//                $this->entityManager->persist($publication);
//                $this->entityManager->flush();
//                $view = $this->view($publication, 201);
//            }else{
//                throw new \Exception("Form error.");
//            }
//        }catch(\Exception $e){
//            $view = $this->view($e->getMessage(), 201);
//            return $this->handleView($view);
//            throw new \Exception("Unable to save new publication at this time.");
//        }
//
//        return $this->handleView($view);
//    }

    /**
     * @Rest\Get("/users/{id}/publications", name="user-publications-list", requirements = {"id"="\d+"})
     */
    public function getUserFeed(int $id, UserRepository $userRepository, PublicationRepository $publicationRepository){
        $dbUser = $userRepository->find($id);
        if(!$dbUser){
            throw new EntityNotFoundException("No user found for id " . $id);
        }
        $publicationRepository->findAll();
        $result = $publicationRepository->getUserFeed($dbUser->getSubscriptions());
        dump($result);exit;
        $view = $this->view($result, 200);
        return $this->handleView($view);
    }
}