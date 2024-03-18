<?php
// src/EMM/UserBundle/Services/UserManager.php

namespace EMM\UserBundle\Services;

use Doctrine\ORM\EntityManager;

class UserManager
{
    private $em;
    private $userRepository;

    /**
     * Constructor para obtener el repo
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository('EMMUserBundle:User');
    }

    /**
     * Función para ver los usuarios inactivos
     */
    public function findInActiveUsers()
    {
        return $this->userRepository->findBy(['isActive' => false]);
    }
}
