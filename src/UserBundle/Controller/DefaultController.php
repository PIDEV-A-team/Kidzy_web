<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    public function adminAction()
    {
        return $this->render('@User/Default/dashboard.html.twig');
    }
    public function maitresseAction()
    {
        return $this->render('@User/Default/maitresse_dashboard.html.twig');
    }

    public function notificationAction()
    {
        $em = $this->getDoctrine()->getManager();
        //$em = $this->getDoctrine()->getManager();
        /*$qb = $em->createQueryBuilder();

        $results = $qb->select('e')
            ->from('KidzyBundle:Facture', 'e')
           ->where('e.status >= :status')
           ->setParameter('status', '0')
            ->getQuery()
            ->getResult();*/
        $query = $em->createQuery(
            'SELECT f
    FROM KidzyBundle:Facture f
    WHERE f.status = :price'
        )->setParameter('price', 0);

        $result = $query->getResult();


        return $this->render('@User/Default/notifications.html.twig',['notif' => $result]);
    }
    public function accountInfo()
    {
        // allow any authenticated user - we don't care if they just
        // logged in, or are logged in via a remember me cookie
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        // ...
    }

    public function resetPassword()
    {
        // require the user to log in during *this* session
        // if they were only logged in via a remember me cookie, they
        // will be redirected to the login page
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // ...
    }
    public function enfantgarde(){

    }

    public function loginMobileAction($username, $password)
    {
        $user_manager = $this->get('fos_user.user_manager');
        $factory = $this->get('security.encoder_factory');

        $data = [
            'type' => 'validation error',
            'title' => 'There was a validation error',
            'errors' => 'username or password invalide'
        ];
        $response = new JsonResponse($data, 400);


        $user = $user_manager->findUserByUsername($username);
        if(!$user)
            return $response;


        $encoder = $factory->getEncoder($user);

        $bool = ($encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt())) ? "true" : "false";
        if($bool=="true")
        {
            $role= $user->getRoles();

            $data=array('type'=>'Login succeed',
                'id'=>$user->getId(),
                'username'=>$user->getUsername(),
                'password'=>$user->getPassword(),
                'role'=>$user->getRoles());
            $response = new JsonResponse($data, 200);
            return $response;

        }
        else
        {
            return $response;

        }
        // return array('name' => $bool);
    }

    public function allUserAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT c
            
        FROM UserBundle:User c '
        );
        $users = $query->getArrayResult();
        $reponse = new Response(json_encode($users));
        $reponse->headers->set('content-Type','application/json');
        return $reponse;
    }


}
