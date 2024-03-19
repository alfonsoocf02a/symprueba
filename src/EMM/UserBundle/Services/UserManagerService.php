<?php
// src/EMM/UserBundle/Services/UserManager.php

namespace EMM\UserBundle\Services;

use Doctrine\ORM\EntityManager;
use EMM\UserBundle\Entity\User;

class UserManagerService
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
        return $this->userRepository->findAll();
        //return $this->userRepository->findBy(['isActive' => false]);




    }


    /**
     * Función para guardar los usuarios
     * @param User $user        Entidad User
     * @param bool $doFlush     Bandera para determinar si se hace flush o no
     * 
     * @return void
     * @author 
     */
    public function saveUser(User $user, bool $doFlush)
    {
        $this->em->persist($user);

        if ($doFlush) {
            $this->em->flush();
        }
    }

    /*
    public function getUsers()
    {

        $result = array(
            'status'        => false,
            'statusCode'    => 401,
            'message'       => $this->translator->trans('No se ha podido realizar la acción'),
            'data'          => array()
        );

        try {
            $users = $this->userRepository->findAll();
        } catch (\Throwable $th) {
            $result['message'] = $this->translator->trans('Ha ocurrido un error');
            return $result;
        }

        if (empty($users)) {
            $result['message'] = $this->translator->trans('No se han podido obtener usuarios');
            return $result;
        }

        return array(
            'status'        => true,
            'statusCode'    => 200,
            'message'       => $this->translator->trans('Se ha realizado correctamente'),
            'data'          => $users
        );
    }
    */
}
