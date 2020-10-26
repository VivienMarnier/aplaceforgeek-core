<?php


namespace App\Controller;


use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRegistrationController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(EntityManagerInterface $entityManager,UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer, ValidatorInterface $validator){
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Rest\Post("/user-registration/registration", name="registration")
     * @param $request
     * @return Response
     */
    public function register(Request $request){
        if($request->get('password') === $request->get('confirmPassword')){
            $user = $this->serializer->deserialize($request->getContent(), User::class,'json');
            $errors = $this->validator->validate($user);
            if($errors->count() > 0){
                $view = $this->view($errors, 500);
            }else{
                try{
                    $user->setPassword($this->passwordEncoder->encodePassword($user,$request->get('password')));
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $view = $this->view('success', 200);
                }catch(\Exception $e){
                    if($e instanceof UniqueConstraintViolationException){
                        $view = $this->view("The email provided already has an account!", 500);
                    }else{
                        $view = $this->view("Unable to save new user at this time.", 500);
                    }
                }
            }
        }else{
            $view = $this->view("Password does not match the password confirmation.", 500);
        }

        return $this->handleView($view);
    }
}