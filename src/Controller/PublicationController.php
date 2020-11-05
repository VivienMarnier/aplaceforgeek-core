<?php


namespace App\Controller;


use App\Entity\Publication;
use App\Exception\ResourceValidationException;
use App\Form\PublicationType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PublicationController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Post("/gamepublications/create", name="create-publication")
     * @ParamConverter("publication", converter="fos_rest.request_body")
     * @param Request $request
     * @return Response
     */
    public function create(Publication $publication){
        dump($publication);exit;
        $publication = $serializer->deserialize($request->getContent(), Publication::class,'json');
        dump($publication);exit;
        $gameId = $request->get('game');
        $dbGame = $gameRepository->find($gameId);

        if(!$dbGame){
            throw new EntityNotFoundException("No game found for id " . $gameId);
        }

        try{
            $publication = new Publication();
            $date = new \DateTime();
            $publication->setDate($date);
            $publication->setAuthor($this->getUser());
            $publication->setGame($dbGame);
            $form = $this->createForm(PublicationType::class, $publication);
            $form->submit($request->request->all());

            if($form->isValid()){
                $this->entityManager->persist($publication);
                $this->entityManager->flush();
                $view = $this->view($publication, 201);
            }
        }catch(\Exception $e){
            $view = $this->view($e->getMessage(), 201);
            return $this->handleView($view);
            throw new \Exception("Unable to save new publication at this time.");
        }

        return $this->handleView($view);
    }

}