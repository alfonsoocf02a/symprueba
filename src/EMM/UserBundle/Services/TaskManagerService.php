<?php
// src/EMM/UserBundle/Services/TaskManagerService.php

namespace EMM\UserBundle\Services;

use Doctrine\ORM\EntityManager;
use EMM\UserBundle\Entity\Task;
use EMM\UserBundle\Entity\TaskRepository;

class TaskManagerService
{
    private $em;
    private $taskRepository;
    private $translator;

    public function __construct(EntityManager $em, $translator)
    {
        $this->em = $em;
        $this->taskRepository = $em->getRepository('EMMUserBundle:Task');
        $this->translator = $translator;
    }


    public function findTasks(array $params, array $form_filters)
    {
        $queryBuilder = $this->taskRepository->createQueryBuilder('t')
            ->join('t.user', 'u');

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
            0 => 't.title',
            1 => 't.createdAt',
            2 => 'u.username',
            3 => 't.status',
            4 => 't.description',
            5 => 't.id'
        ];

        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';
        $queryBuilder->orderBy($columns[$orderColumnIndex], $orderDir);

        // Paginación
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $queryBuilder->setFirstResult($start)
            ->setMaxResults($length);

        $query = $queryBuilder->getQuery();
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, $fetchJoinCollection = true);

        $totalData = count($this->taskRepository->findAll());
        $totalFiltered = count($paginator);

        // Mapeo de resultados a formato adecuado para DataTables
        $data = array_map(function ($task) {
            return [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'user' => $task->getUser()->getUsername(),
                'status' => $task->getStatus(),
                'description' => $task->getDescription(),
            ];
        }, iterator_to_array($paginator));

        return [
            'draw' => intval($params['draw']),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered, // Aquí necesitarás un método para calcular esto si aplicas filtros
            'data' => $data,
        ];
    }

    public function createTask(Task $task, $form)
    {

        $result = array(
            'status'        => false,
            'statusCode'    => 401,
            'message'       => $this->translator->trans('No se ha podido creaar la tarea'),
            'data'          => array()
        );


        try {
            if ($form->isValid()) {
                $task->setStatus(0);
                $this->em->persist($task);
                $this->em->flush();
            }
        } catch (\Throwable $th) {
            $result['message'] = $this->translator->trans('Error en la base de datos');
            return $result;
        }

        return array(
            'status'        => true,
            'statusCode'    => 200,
            'message'       => $this->translator->trans('Tarea creada correctamente'),
            'data'          => array()
        );
    }

    public function getTask($id)
    {

        $result = array(
            'status'        => false,
            'statusCode'    => 401,
            'message'       => $this->translator->trans('No se ha podido realizar la acción'),
            'data'          => array()
        );

        try {
            $task = $this->taskRepository->find($id);
        } catch (\Throwable $th) {
            $result['message'] = $this->translator->trans('Ha ocurrido un error');
            return $result;
        }

        if (empty($task)) {
            $result['message'] = $this->translator->trans('No se han podido obtener usuarios');
            return $result;
        }

        return array(
            'status'        => true,
            'statusCode'    => 200,
            'message'       => $this->translator->trans('Se ha realizado correctamente'),
            'data'          => $task
        );
    }


    public function updateTask(Task $task, $form)
    {

        $result = array(
            'status'        => false,
            'statusCode'    => 401,
            'message'       => $this->translator->trans('No se ha podido actualizar la tarea'),
            'data'          => array()
        );


        try {
            if ($form->isValid()) {
                $task->setStatus(0);
                $task->setStatus(0);
                $this->em->flush();
            }
        } catch (\Throwable $th) {
            $result['message'] = $this->translator->trans('Error en la base de datos');
            return $result;
        }

        return array(
            'status'        => true,
            'statusCode'    => 200,
            'message'       => $this->translator->trans('Tarea actualizada correctamente'),
            'data'          => array()
        );
    }


    public function deleteTask(Task $task)
    {

        $result = array(
            'status'        => false,
            'statusCode'    => 401,
            'message'       => $this->translator->trans('No se ha podido eliminar la tarea'),
            'data'          => array()
        );


        try {
            $this->em->remove($task);
            $this->em->flush();
        } catch (\Throwable $th) {
            $result['message'] = $this->translator->trans('Error en la base de datos');
            return $result;
        }

        return array(
            'status'        => true,
            'statusCode'    => 200,
            'message'       => $this->translator->trans('Tarea eliminada correctamente'),
            'data'          => array()
        );
    }
}
