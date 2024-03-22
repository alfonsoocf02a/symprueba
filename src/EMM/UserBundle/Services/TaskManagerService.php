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
}
