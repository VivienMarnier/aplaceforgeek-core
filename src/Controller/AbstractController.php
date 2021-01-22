<?php


namespace App\Controller;


use App\Exception\ResourceValidationException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AbstractController extends AbstractFOSRestController
{
    /**
     * @param ConstraintViolationListInterface $violations
     * @throws ResourceValidationException
     */
    protected function handleViolations(ConstraintViolationListInterface $violations){
        if(count($violations) > 0){
            $message = "The JSON sent contains invalid data.";
            foreach($violations as $violation){
                $message .= $violation;
            }

            throw new ResourceValidationException($message);
        }
    }
}