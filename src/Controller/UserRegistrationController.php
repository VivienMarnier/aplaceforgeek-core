<?php


namespace App\Controller;


use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

class UserRegistrationController extends AbstractFOSRestController
{
    /**
     * @Rest\Route("/user-registration/test", methods={"GET"}, name="registration")
     */
    public function registrationAction(){

        $data = ['prenom' => 'vivien', 'nom' => 'marnier', 'age' => '28'];
        $view = $this->view($data, 200);

        return $this->handleView($view);
    }
}