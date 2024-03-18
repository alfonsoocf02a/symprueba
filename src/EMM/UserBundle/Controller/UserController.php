<?php

namespace EMM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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


        //Usando mi controlador personalizado
        $userManager = $this->get('emm.user_bundle.user_manager');
        $users = $userManager->findInActiveUsers();

        return $this->render('EMMUserBundle:User:index.html.twig', array('users' => $users));
    }

    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('EMMUserBundle:User');

        $user = $repository->find($id);

        // $user = $repository->findOneByUsername($nombre);

        return new Response('Usuario: ' . $user->getUsername() . ' con email: ' . $user->getEmail());
    }
}
