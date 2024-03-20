<?php
// src/EMM/UserBundle/Services/UserManager.php

namespace EMM\UserBundle\Services;

use Doctrine\ORM\EntityManager;
use EMM\UserBundle\Entity\User;
use EMM\UserBundle\Entity\UserRepository;

class UserManagerService
{
    private $em;
    private $userRepository;
    private $translator;

    /**
     * Constructor para obtener el repo
     */
    public function __construct(EntityManager $em, $translator)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository('EMMUserBundle:User');
        $this->translator = $translator;
    }

    /**
     * Función para ver los usuarios inactivos
     * 
     * @param array $params           Entidad User
     * @param array $form_filters     Bandera para determinar si se hace flush o no
     * 
     * @return array
     * @author Alfonso
     */
    // UserManagerService.php
    public function findInActiveUsers($params, $form_filters)
    {
        $queryBuilder = $this->userRepository->createQueryBuilder('u');

        // Aplicar Filtros
        if (!empty($form_filters)) {
            foreach ($form_filters as $field => $value) {
                if (!empty($value)) {
                    // Supongamos que $form_filters contiene filtros directamente relacionados con los campos
                    $queryBuilder->andWhere("u.$field LIKE :$field")
                        ->setParameter($field, '%' . $value . '%');
                }
            }
        }

        // Ordenación
        $columns = [
            0 => "u.username",
            1 => "u.firstName",
            2 => "u.lastName",
            3 => "u.email",
            4 => 'u.role',
            5 => 'u.createdAt',
            6 => 'u.updatedAt',
        ];

        if (isset($params['order']) && count($params['order'])) {
            $orderBy = $columns[$params['order'][0]['column']] ?? 'u.createdAt';
            $dir = $params['order'][0]['dir'] ?? 'asc';
            $queryBuilder->orderBy($orderBy, $dir);
        }

        // Paginación
        if (isset($params['start']) && isset($params['length']) && $params['length'] != -1) {
            $queryBuilder->setFirstResult($params['start'])
                ->setMaxResults($params['length']);
        }

        $query = $queryBuilder->getQuery();
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, $fetchJoinCollection = true);

        $totalData = count($this->userRepository->findAll());
        $totalFiltered = count($paginator);

        $data = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'), // Asegúrate de que las fechas se convierten correctamente
                'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }, iterator_to_array($paginator));

        return [
            'draw' => intval($params['draw']),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];
    }



    /**
     * Función para guardar los usuarios
     * @param User $user        Entidad User
     * @param bool $doFlush     Bandera para determinar si se hace flush o no
     * 
     * @return void
     * @author 
     */
    public function saveUser(User $user)
    {
        $this->em->persist($user);

        // Hacer que el flush solo se haga al final de todo para evitar
        // llenar el buffer. En este caso lo dejo así por que solo se inserta uno
        $this->em->flush();
    }


    public function getUser($id)
    {

        $result = array(
            'status'        => false,
            'statusCode'    => 401,
            'message'       => $this->translator->trans('No se ha podido realizar la acción'),
            'data'          => array()
        );

        try {
            $users = $this->userRepository->find($id);
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
}
