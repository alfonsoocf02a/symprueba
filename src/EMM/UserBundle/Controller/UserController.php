<?php

namespace EMM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use EMM\UserBundle\Entity\User;
use EMM\UserBundle\Form\UserType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManager;

class UserController extends Controller
{
    /**
     * Función para mostrar el listado de usuario
     * @return Response 
     */
    // UserController.php


    public function homeAction()
    {
        return $this->render("EMMUserBundle:User:home.html.twig");
    }

    public function renderindexAction(Request $request)
    {
        return $this->render('EMMUserBundle:User:index.html.twig');
    }

    public function indexAction(Request $request)
    {

        $userManager = $this->get('emm.user_bundle.user_manager_service');

        $params = $request->request->all();

        //Recogemos los filtros en un array
        $form_filt = $request->request->get('form_filters');
        $form_filters = [];
        parse_str($form_filt, $form_filters);

        //Obtenemos la información de los clientes
        $users = $userManager->findInActiveUsers($params, $form_filters);
        return new JsonResponse($users);
    }


    /**
     * Función para añadir usuario
     * @return Response 
     */
    public function addAction()
    {
        $user = new User();
        $form = $this->createCreateForm($user);

        return $this->render('EMMUserBundle:User:add.html.twig', array('form' => $form->createView()));
    }

    /**
     * Función para crear un formulario (para añadir un usuario)
     * @return mixed
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('emm_user_create'),
            'method' => 'POST'
        ));

        return $form;
    }

    /**
     * Función validar y persistir la creacion de un usuario a partir de una request del formulario
     * @return mixed
     */
    public function createAction(Request $request)
    {

        /*
        Este código inicializa una nueva instancia de la entidad User, crea y configura un formulario vinculado a esta entidad, 
        y luego procesa la petición HTTP para asignar automáticamente los datos del formulario enviado a las propiedades 
        del objeto User, listo para ser validado y guardado.
        */

        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $form->get('password')->getData();

            $passwordConstraint = new Assert\NotBlank();
            $errorList = $this->get('validator')->validate($password, $passwordConstraint);

            if (count($errorList) == 0) {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);

                $user->setPassword($encoded);

                //Usando mi controlador personalizado
                $userManager = $this->get('emm.user_bundle.user_manager_service');
                $userManager->saveUser($user);

                $successMessage = $this->get('translator')->trans('The user has been created.');
                $this->addFlash('mensaje', $successMessage);

                return $this->redirectToRoute('emm_user_index');
            } else {
                $errorMessage = new FormError($errorList[0]->getMessage());
                $form->get('password')->addError($errorMessage);
            }

            //$em = $this->getDoctrine()->getManager();
            //$em->persist($user);
            //$em->flush();

        }

        return $this->render('EMMUserBundle:User:add.html.twig', array('form' => $form->createView()));
    }

    public function editAction($id) //Lo llamo desde la vista
    {
        //Usando mi controlador personalizado
        $userManager = $this->get('emm.user_bundle.user_manager_service');
        $userResponse = $userManager->getUser($id);

        if ($userResponse['status'] == false) {
            $messageException = json_encode($userResponse['message']);
            throw $this->createNotFoundException($messageException);
        }

        $user = $userResponse['data'];

        if (!$user) {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException($messageException);
        }

        //Creamos y renderizamos el formulario de edicion con los datos del usuario
        $form = $this->createEditForm($user);

        return $this->render('EMMUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    private function createEditForm(User $entity)
    {
        //Cuando submit, llama a updateAction
        $form = $this->createForm(new UserType(), $entity, array('action' => $this->generateUrl('emm_user_update', array('id' => $entity->getId())), 'method' => 'PUT'));

        return $form;
    }

    public function updateAction($id, Request $request)
    {

        $userManager = $this->get('emm.user_bundle.user_manager_service');
        $userResponse = $userManager->getUser($id);

        if ($userResponse['status'] == false) {
            throw $this->createNotFoundException($userResponse['message']);
        }

        //Obtenemos los datos de la respuesta del formulario recibodo
        $form = $this->createEditForm($userResponse['data'])->handleRequest($request);

        try {
            $ResponseGeneral = $userManager->updateUser($id, $form);
        } catch (\Throwable $th) {
            throw new \Exception("Error haciendo la actualización");
        }

        if ($ResponseGeneral['status'] == true) {
            $this->addFlash('mensaje', $ResponseGeneral['message']);
            return $this->redirectToRoute('emm_user_index', array('id' => $userResponse['data']->getId()));
        }
        return $this->render('EMMUserBundle:User:edit.html.twig', array('user' => $userResponse['data'], 'form' => $form->createView()));
    }

    public function viewAction($id)
    {

        //Obtenemos el usuario
        $userManager = $this->get('emm.user_bundle.user_manager_service');
        $userResponse = $userManager->getUser($id);

        if ($userResponse['status'] == false) {
            $messageException = json_encode($userResponse['message']);
            throw $this->createNotFoundException($messageException);
        }

        $user = $userResponse['data'];

        $deleteForm = $this->createDeleteForm($user);

        return $this->render('EMMUserBundle:User:view.html.twig', array('user' => $user, 'delete_form' => $deleteForm->createView()));
    }

    private function createDeleteForm($user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('emm_user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    public function deleteAction(Request $request, $id)
    {
        //Obtenemos el usuario
        $userManager = $this->get('emm.user_bundle.user_manager_service');
        $userResponse = $userManager->getUser($id);

        if ($userResponse['status'] == false) {
            $messageException = json_encode($userResponse['message']);
            throw $this->createNotFoundException($messageException);
        }

        //Obtenemos los datos de la respuesta del formulario recibodo
        $form = $this->createDeleteForm($userResponse['data'])->handleRequest($request);

        try {
            $ResponseGeneral = $userManager->deleteUser($id, $form);
        } catch (\Throwable $th) {
            throw new \Exception("Error eliminando el usuario");
        }

        //mostramos el resultado

        $this->addFlash('mensaje', $ResponseGeneral['message']);
        return $this->redirectToRoute('emm_user_index');
    }

    public function viewpokeAction()
    {
        return $this->render("EMMUserBundle:User:pokeindex.html.twig");
    }
}
