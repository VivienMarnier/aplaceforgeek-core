<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
     private $passwordEncoder;

     public function __construct(UserPasswordEncoderInterface $passwordEncoder)
     {
         $this->passwordEncoder = $passwordEncoder;
     }

    public function load(ObjectManager $manager)
    {
        $fixtures = [
            'vivienmarnier@gmail.com' => ['ROLE_ADMIN','ROLE_USER'],
            'test@gmail.com' => ['ROLE_USER'],
        ];

        foreach($fixtures as $email => $roles){
            $user = new User();
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setPassword($this->passwordEncoder->encodePassword($user,'password'));
            $user->setActive(true);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
