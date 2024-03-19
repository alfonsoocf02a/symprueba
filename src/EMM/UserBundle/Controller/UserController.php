<?php

namespace EMM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use EMM\UserBundle\Entity\User;
use EMM\UserBundle\Form\UserType;

class UserController extends Controller
{
    /**
     * Función para mostrar el listado de usuario
     * @return Response 
     */
    public function indexAction()
    {

        //return new Response('Bienvenido a mi módulo de usuarios');

        //$em = $this->getDoctrine()->getManager();

        //$users = $em->getRepository('EMMUserBundle:User')->findAll();

        /*

        $res = 'Lista de usuarios: <br />';

        foreach ($users as $user) {
            $res .= 'Usuario: ' . $user->getUsername() . ' - Email: ' . $user->getEmail() . '<br />';
        }

        return new Response($res);

        */


        //Usando mi controlador
        $userManager = $this->get('emm.user_bundle.user_manager_service');
        $users = $userManager->findInActiveUsers();

        return $this->render('EMMUserBundle:User:index.html.twig', array('users' => $users['data']));
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

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $password);

            $user->setPassword($encoded);

            //$em = $this->getDoctrine()->getManager();
            //$em->persist($user);
            //$em->flush();

            //Usando mi controlador personalizado
            $userManager = $this->get('emm.user_bundle.user_manager');
            $userManager->saveUser($user);

            return $this->redirectToRoute('emm_user_index');
        }

        return $this->render('EMMUserBundle:User:add.html.twig', array('form' => $form->createView()));
    }


    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('EMMUserBundle:User');

        $user = $repository->find($id);

        // $user = $repository->findOneByUsername($nombre);

        return new Response('Usuario: ' . $user->getUsername() . ' con email: ' . $user->getEmail());
    }
}
