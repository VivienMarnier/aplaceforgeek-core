<?php


namespace App\Controller;


use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

class UserRegistrationController extends AbstractFOSRestController
{
    /**
     * @Rest\Post("/user-registration/test", name="registration")
     */
    public function registrationAction(Request $request){

        $data = ['prenom' => 'vivien', 'nom' => 'marnier', 'age' => '28'];
        $view = $this->view($data, 200);

        return $this->handleView($view);
    }
}