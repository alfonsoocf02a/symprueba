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

    public function renderindexAction(Request $request)
    {

        $userManager = $this->get('emm.user_bundle.user_manager_service');
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


    public function getUsersAction(Request $request)
    {

        $users = $this->get('user_service')->getAll();

        return new JsonResponse(
            $users['data'],
            $users['statusCode']
        );
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
     * Función para crear un formulario
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
     * Función validar y persistir un usuario
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

    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('EMMUserBundle:User')->find($id);

        //Usando mi controlador personalizado
        $userManager = $this->get('emm.user_bundle.user_manager_service');
        $userManager->saveUser($user);

        if (!$user) {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException($messageException);
        }

        $form = $this->createEditForm($user);

        return $this->render('EMMUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    private function createEditForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array('action' => $this->generateUrl('emm_user_update', array('id' => $entity->getId())), 'method' => 'PUT'));

        return $form;
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('EMMUserBundle:User')->find($id);

        //Usando mi controlador personalizado
        //$userManager = $this->get('emm.user_bundle.user_manager_service');
        //$user = $userManager->getUser($id);

        if (!$user) {
            $messageException = $this->get('translator')->trans('User not found.');
            throw $this->createNotFoundException($messageException);
        }

        $form = $this->createEditForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            if (!empty($password)) {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $password);
                $user->setPassword($encoded);
            } else {
                $recoverPass = $this->recoverPass($id);
                $user->setPassword($recoverPass[0]['password']);
            }

            if ($form->get('role')->getData() == 'ROLE_ADMIN') {
                $user->setIsActive(1);
            }

            $em->flush();

            $successMessage = $this->get('translator')->trans('The user has been modified.');
            $this->addFlash('mensaje', $successMessage);
            return $this->redirectToRoute('emm_user_edit', array('id' => $user->getId()));
        }
        return $this->render('EMMUserBundle:User:edit.html.twig', array('user' => $user, 'form' => $form->createView()));
    }

    private function recoverPass($id)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT u.password
            FROM EMMUserBundle:User u
            WHERE u.id = :id'
        )->setParameter('id', $id);

        $currentPass = $query->getResult();

        return $currentPass;
    }
}
