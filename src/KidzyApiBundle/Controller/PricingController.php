<?php

namespace KidzyApiBundle\Controller;

use KidzyBundle\Entity\Facture;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class PricingController extends Controller
{
    public function allPackAction()
    {
        $packs = $this->getDoctrine()->getManager()
            ->getRepository('KidzyBundle:Pack')
            ->findAll();

        return new Response(json_encode($packs));
    }
    public function findPackAction($id)
    {
        $packs = $this->getDoctrine()->getManager()
            ->getRepository('KidzyBundle:Pack')
            ->find($id);

        return new Response(json_encode($packs));
    }
    public function allFactureAction()
    {
        $factures = $this->getDoctrine()->getManager()
            ->getRepository('KidzyBundle:Facture')
            ->findAll();

        return new Response(json_encode($factures));
    }
    public function findFactureAction($id)
    {
        $factures = $this->getDoctrine()->getManager()
            ->getRepository('KidzyBundle:Facture')
            ->find($id);

        return new Response(json_encode($factures));
    }
    public function newFactureAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $facture = new Facture();

        $iduser = $request->get('idparent');
        $user = $em->getRepository('UserBundle:User')->find($iduser);

        $idEnfant = $request->get('idEnfant');
        $enfant = $em->getRepository('KidzyBundle:Enfant')->find($idEnfant);

        $idpack = $request->get('idPack');
        $pack = $em->getRepository('KidzyBundle:Pack')->find($idpack);

        $prix = $request->get('prix');
        $due =$request->get('end') ;

        $facture->setDateFacture(new \DateTime());
        $facture->setDue_dateFacture($due);
        $facture->setPack($pack);
        $facture->setPaye(false);
        $facture->setTotal($prix);
        $facture->setIdParent($user);
        $facture->setIdEnf($enfant);
        $facture->setStatus(0);
        $em->persist($facture);
        $em->flush();


        return new Response(json_encode($facture));
    }
    public function createnewFactureAction($idpack,$iduser,$idEnfant,$prix,$due)
    {
        $em = $this->getDoctrine()->getManager();
        $facture = new Facture();


        $user = $em->getRepository('UserBundle:User')->find($iduser);


        $enfant = $em->getRepository('KidzyBundle:Enfant')->find($idEnfant);


        $pack = $em->getRepository('KidzyBundle:Pack')->find($idpack);

        $facture->setDateFacture(new \DateTime());
        $facture->setDue_dateFacture($due);
        $facture->setPack($pack);
        $facture->setPaye(false);
        $facture->setTotal($prix);
        $facture->setIdParent($user);
        $facture->setIdEnf($enfant);
        $facture->setStatus(0);
        $em->persist($facture);
        $em->flush();

        return new Response(json_encode($facture));
    }
    public function finduserprAction($id)
    {   $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:User')->find($id);
        $enfants = $em->getRepository('KidzyBundle:Enfant')->findBy(array('idParent' => $user));

        return new Response(json_encode($enfants));

    }
    public function findfacturebyenfantAction($id)
    {   $em = $this->getDoctrine()->getManager();
        $enfant = $em->getRepository('KidzyBundle:Enfant')->find($id);
        $factures = $em->getRepository('KidzyBundle:Facture')->findBy(array('idEnf' => $enfant));

        return new Response(json_encode($factures));

    }
    public function updatefactureAction($id,$paydate)
    {
        $Date = new \DateTime($paydate);
        $entityManager = $this->getDoctrine()->getManager();
        $facture = $entityManager->getRepository('KidzyBundle:Facture')->find($id);

        if (!$facture) {
            throw $this->createNotFoundException(
                'No invoice found for id '.$id
            );
        }

        $facture->setPayedate($Date);
        $entityManager->flush();
        return new Response(json_encode($facture));
    }
    public function payupdateAction(Request $request )
    {
        $end = $request->get('due');
        $prix = $request->get('amount');
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('idPack');
        $idfacture = $request->get('idfacture');
        $idEnfant = $request->get('enfant');
        $pack = $em->getRepository('KidzyBundle:Pack')->find($id);
        $enfant = $em->getRepository('KidzyBundle:Enfant')->find($idEnfant);
        $user = $this->container->get('security.token_storage')->getToken()->getUser();


        \Stripe\Stripe::setApiKey('sk_test_8TNB5HaJ0H5lWP5qMso3OWDI00syLPhFY3');

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'name' => $pack->getNomPack(),
                'description' => $pack->getDescriptionPack(),
                'images' => ['https://image.freepik.com/free-vector/e-mail-news-subscription-promotion-flat-vector-illustration-design-newsletter-icon-flat_1200-330.jpg'],
                'amount' => $request->get('amount')*100,
                'currency' => 'usd',
                'quantity' => 1,
            ]],
            'success_url' => 'http://localhost/kidzy_web/web/app_dev.php/api/facture/payment/success/'.$idfacture.'/'.$id.'/'.$idEnfant.'/'.$prix.'/'.$end.'/{CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost/kidzy_web/web/app_dev.php'
        ]);


        return $this->render('@KidzyApi/Default/updatepayment.html.twig' , array('pack' => $pack , 'CHECKOUT_SESSION_ID'=>$session->id ,'enfant' =>$enfant ));
    }

    public function updateinvoicesuccessAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $idfacture = $request->get('idfacture');
        $facture = $em->getRepository('KidzyBundle:Facture')->find($idfacture);
        $facture->setPaye(true);
        $em->flush();

        return $this->render('@KidzyApi/Default/updatesuccess.html.twig' , array('facture' => $facture));
    }
    public function sendmailAction( $id , $iduser)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $facture = $entityManager->getRepository('KidzyBundle:Facture')->find($id);
        $user = $entityManager->getRepository('UserBundle:User')->find($iduser);
        $message = (new \Swift_Message('UNPAID INVOICE'))
            ->setFrom(['support@kidzy.tn' => 'Kidzy'])
            ->setTo($user->getEmail());
        $img = $message->embed(\Swift_Image::fromPath('front/images/unnamed.png'));
           $message ->setBody(
                $this->renderView(

                    '@KidzyApi/Default/payedate.html.twig',
                    array('img' => $img,'facture' => $facture)

                ),
                'text/html'
            );

        $this->get('mailer')->send($message);
        return new Response('<html<body>Sent</body></html>');



    }


}


