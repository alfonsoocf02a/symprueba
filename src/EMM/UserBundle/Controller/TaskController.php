<?php

namespace EMM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EMM\UserBundle\Entity\Task;
use EMM\UserBundle\Form\TaskType;
use Symfony\Component\HttpFoundation\JsonResponse;
use EMM\UserBundle\Entity\User;
use EMM\UserBundle\Form\UserType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManager;


class TaskController extends Controller
{

    public function renderindexAction(Request $request)
    {
        return $this->render('EMMUserBundle:Task:index.html.twig');
    }

    public function indexDataAction(Request $request)
    {
        $taskManager = $this->get('emm.user_bundle.task_manager_service');

        // Accediendo a todos los parámetros de DataTables enviados vía POST
        $params = $request->request->all();

        // Suponiendo que los filtros forman parte de los parámetros
        // Si tienes un mecanismo de filtrado personalizado, ajusta según sea necesario
        $form_filters = isset($params['form_filters']) ? $params['form_filters'] : [];

        $tasks = $taskManager->findTasks($params, $form_filters);

        return new JsonResponse($tasks);
    }


    public function addAction()
    {
        $task = new Task();
        $form = $this->createCreateForm($task);

        return $this->render('EMMUserBundle:Task:add.html.twig', array('form' => $form->createView()));
    }

    private function createCreateForm(Task $entity)
    {
        $form = $this->createForm(new TaskType(), $entity, array(
            'action' => $this->generateUrl('emm_task_create'),
            'method' => 'POST'
        ));

        return $form;
    }

    public function createAction(Request $request)
    {
        $task = new Task();
        $form = $this->createCreateForm($task);
        $form->handleRequest($request);

        $taskManager = $this->get('emm.user_bundle.task_manager_service');

        $taskResponse = $taskManager->createTask($task, $form);

        if ($taskResponse['status'] == false) {
            $this->addFlash('mensaje', $taskResponse['message']);
            return $this->render('EMMUserBundle:Task:add.html.twig', array('form' => $form->createView()));
        }

        $this->addFlash('mensaje', $taskResponse['message']);
        return $this->redirectToRoute('emm_task_index');
    }


    public function viewAction($id)
    {
        $task = $this->getDoctrine()->getRepository('EMMUserBundle:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException('The task does not exist.');
        }

        $deleteForm = $this->createCustomForm($task->getId(), 'DELETE', 'emm_task_delete');

        $user = $task->getUser();

        return $this->render('EMMUserBundle:Task:view.html.twig', array('task' => $task, 'user' => $user, 'delete_form' => $deleteForm->createView()));
    }

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $task = $em->getRepository('EMMUserBundle:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException('task not found');
        }

        $form = $this->createEditForm($task);

        return $this->render('EMMUserBundle:Task:edit.html.twig', array('task' => $task, 'form' => $form->createView()));
    }

    private function createEditForm(Task $entity)
    {
        $form = $this->createForm(new TaskType(), $entity, array(
            'action' => $this->generateUrl('emm_task_update', array('id' => $entity->getId())),
            'method' => 'PUT'
        ));

        return $form;
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $task = $em->getRepository('EMMUserBundle:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException('task not found');
        }

        $form = $this->createEditForm($task);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $task->setStatus(0);
            $em->flush();
            $this->addFlash('mensaje', 'The task has been modified');
            return $this->redirectToRoute('emm_task_edit', array('id' => $task->getId()));
        }

        return $this->render('EMMUserBundle:Task:edit.html.twig', array('task' => $task, 'form' => $form->createView()));
    }

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $task = $em->getRepository('EMMUserBundle:Task')->find($id);

        if (!$task) {
            throw $this->createNotFoundException('task not found');
        }

        $form = $this->createCustomForm($task->getId(), 'DELETE', 'emm_task_delete');
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $em->remove($task);
            $em->flush();

            $this->addFlash('mensaje', 'The task has been deleted');

            return $this->redirectToRoute('emm_task_index');
        }
    }

    private function createCustomForm($id, $method, $route)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl($route, array('id' => $id)))
            ->setMethod($method)
            ->getForm();
    }
}
