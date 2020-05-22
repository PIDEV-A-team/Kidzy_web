<?php

namespace KidzyBundle\Controller;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use KidzyBundle\Entity\Club;
use KidzyBundle\Entity\Enfant;
use KidzyBundle\Entity\Event;
use KidzyBundle\Entity\Inscription;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Club controller.
 *
 */
class ClubController extends Controller
{
    /**
     * Lists all Club entities.
     *
     */
    public function showEventAction( $idClub)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getManager()->getRepository(Event::class);
        $event=$repository->myfinfEvent($idClub);
        $clubs = $em->getRepository('KidzyBundle:Club')->find($idClub);

        return $this->render('@Kidzy/club/EventFront.html.twig', array(
            'event' => $event,
            'club' => $clubs

        ));
    }

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $club = $em->getRepository('KidzyBundle:Club')->findAll();

        return $this->render('@Kidzy/club/index.html.twig', array(
            'club' => $club,
        ));
    }
    public function indexClubAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $club = $em->getRepository('KidzyBundle:Club')->findAll();
        $clubs= $this->get('knp_paginator')->paginate($club, $request->query->get( 'page',  1), 2);
        return $this->render('@Kidzy/club/AutreClubFront.html.twig', array(
            'club' => $club,
            'club' => $clubs,

        ));
    }
    public function indexParentAction(Request $request)
    {   $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $idParent = $user->getId();
        $repository = $this->getDoctrine()->getManager()->getRepository(Inscription::class);
        $listenfants=$repository->myfinfClub($idParent);
        $clubs= $this->get('knp_paginator')->paginate($listenfants, $request->query->get( 'page',  1), 3);


        return $this->render('@Kidzy/club/ClubFront.html.twig', array(
            'club' => $listenfants,            'club' => $clubs,

        ));
    }
    /**
     * Creates a new club entity.
     *
     */


    public function newAction(Request $request)
    {
        $club = new Club();
        $form = $this->createForm('KidzyBundle\Form\ClubType', $club);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($club);
            $em->flush();


            return $this->redirectToRoute('club');
        }

        return $this->render('@Kidzy/club/new.html.twig', array(
            'club' => $club,
            'form' => $form->createView(),
        ));
    }
    public function showAction(Request $request,Club $club)
    {
        $deleteForm = $this->createDeleteForm($club);
        $repository = $this->getDoctrine()->getManager()->getRepository(Inscription::class);
        $idClub = $request->get('idClub');

        $nbrenfants=$repository->myfinfnbre($idClub);

        if ($nbrenfants==0){
            $nb=0;
        }else { $nb=$nbrenfants;}
        return $this->render('@Kidzy/club/show.html.twig', array(
            'club' => $club,
            'nbre' => $nb,
            'delete_form' => $deleteForm->createView()
        ));
    }

    public function deleteAction(Request $request, Club $club)
    {
        $form = $this->createDeleteForm( $club);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove( $club);
            $em->flush();

        }

        return $this->redirectToRoute('club');
    }
    private function createDeleteForm(Club $club)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('club_delete', array('idClub' => $club->getIdClub())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }

    public function editAction(Request $request, Club $club)
    {
        $Form = $this->createDeleteForm($club);
        $editForm = $this->createForm('KidzyBundle\Form\ClubType', $club);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', 'Club modifié avec succés');

            return $this->redirectToRoute('club_edit', array('idClub' => $club->getIdClub()));
        }else {}

        return $this->render('@Kidzy/club/edit.html.twig', array(
            'club' => $club,
            'edit_form' => $editForm->createView(),
            'delete_form' => $Form->createView(),
        ));
    }
    public function chartsAction()
    {
        $pieChart = new PieChart();
        $em = $this->getDoctrine()->getManager();

        $club = $em->getRepository('KidzyBundle:Club')->findAll();
        $repository = $this->getDoctrine()->getManager()->getRepository(Club::class);
        $listes= $repository->myfinfnbres();
        $data=array();
        $a=['nomClub', 'NB'];
        array_push($data,$a);
        foreach($listes as $c) {

            $a=array($c['nomClub'],$c['NB']);
            array_push($data,$a);

        }
        $pieChart->getData()->setArrayToDataTable(
            $data
        );
        $pieChart->getOptions()->setTitle('Clubs ');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(900);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);

        return $this->render('@Kidzy/club/Chart.html.twig', array('piechart' => $pieChart));
    }
    public function printAction(Request $request)
    {
        $idClub = $request->get('idClub');
        $idEnfant = $request->get('idEnfant');
        $idInscrit= $request->get('idInscrit');
        $em = $this->getDoctrine()->getManager();
        $club = $em->getRepository('KidzyBundle:Club')->find($idClub);
        $enfant = $em->getRepository('KidzyBundle:Enfant')->find($idEnfant);
        $inscrit = $em->getRepository('KidzyBundle:Inscription')->find($idInscrit);


        $html = $this->renderView('@Kidzy/club/print.html.twig', array(
            'enfant'  => $enfant,
            'club' => $club,
            'inscrit' => $inscrit
        ));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'attestation.pdf'
        );
    }




    public function allClubAction()
    {
        $em = $this->getDoctrine()->getManager();

        $club = $em->getRepository('KidzyBundle:Club')->findAll();
        $serializer= new  Serializer([new ObjectNormalizer()]);
        $formatted=$serializer->normalize($club);
        return new JsonResponse($formatted);

        return $this->render('@Kidzy/club/index.html.twig', array(
            'club' => $club,
        ));
    }
    public function indexParentMobileAction($idParent)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(Club::class);
        $listenfants=$repository->myfinfClubMobile($idParent);

        $serializer= new  Serializer([new ObjectNormalizer()]);
        $formatted=$serializer->normalize($listenfants);
        return new JsonResponse($formatted);




    }
    public function indexAutreMobileAction()
    {
        $em = $this->getDoctrine()->getManager();

        $club = $em->getRepository('KidzyBundle:Club')->findAll();

        $serializer= new  Serializer([new ObjectNormalizer()]);
        $formatted=$serializer->normalize($club);
        return new JsonResponse($formatted);




    }
    public function indexTestMobileAction($idParent)
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(Club::class);
        $listenfants=$repository->myfinfAutreClubMobile($idParent);

        $serializer= new  Serializer([new ObjectNormalizer()]);
        $formatted=$serializer->normalize($listenfants);
        return new JsonResponse($formatted);



    }
    public function StatMobileAction()
    {

        $repository = $this->getDoctrine()->getManager()->getRepository(Club::class);
        $listes= $repository->myfinfnbres();


        $serializer= new  Serializer([new ObjectNormalizer()]);
        $formatted=$serializer->normalize($listes);
        return new JsonResponse($formatted);



    }
    public function showParentMobileAction($idInscrit,$idClub,$idEnfant)
    {


        $repository = $this->getDoctrine()->getManager()->getRepository(Inscription::class);


        $details=$repository->myfinfClubDetails($idClub,$idEnfant,$idInscrit);
        $serializer= new  Serializer([new ObjectNormalizer()]);
        $formatted=$serializer->normalize( $details);
        return new JsonResponse($formatted);



    }
    public function EnfantMobileAction($idParent)
    {


        $repository = $this->getDoctrine()->getManager()->getRepository(Enfant::class);


        $details=$repository->myfinfTestMobile($idParent);
        $serializer= new  Serializer([new ObjectNormalizer()]);
        $formatted=$serializer->normalize( $details);
        return new JsonResponse($formatted);



    }
    public function deleteMobileAction($idInscrit)
    {
        $em = $this->getDoctrine()->getManager();
        $inscrit = $em->getRepository('KidzyBundle:Inscription')->find($idInscrit);
        $em->remove( $inscrit);
        $em->flush();



        return $this->json(array ('title'=>'successful','message'=>"Inscription supprimé"),200);
    }

    public function InsertMobileAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $inscrit = new Inscription();
        $today = new \DateTime('now');
        $idClub=$request->get('idClub');
        $idEnfant=$request->get('idEnfant');
        // wini el fonction eli tkhdem fel symfony
        $enfant = $em->getRepository('KidzyBundle:Enfant')->find($idEnfant);

        $club = $em->getRepository('KidzyBundle:Club')->find($idClub);
        $inscrit->setDateInscrit($today);
        $inscrit->setIdClub($club);
        $inscrit->setEnfant($enfant);
        $inscrit->setDescriptionInscrit($request->get('descriptionInscrit'));
        $repository = $this->getDoctrine()->getManager()->getRepository(Inscription::class);

        $existe=$repository->myfinfInsc($idEnfant,$idClub);
        if ( !$existe) {

            $em->persist($inscrit);
            $em->flush();
        } else
        {
            $data = [
                'title' => 'Erreur',
                'message' => 'deja in scrit',
                //'errors' => $ex->getMessage()
            ];
            $response = new JsonResponse($data,400);
            return $response;
        }
        return $this->json(array('title'=>'successful','message'=> " successfully"),200);


        // $serializer= new  Serializer([new ObjectNormalizer()]);
        // $formatted=$serializer->normalize( $inscrit);
        //return new JsonResponse($formatted);



    }
    public function printMobileAction(Request $request)
    {
        $idClub = $request->get('idClub');
        $idEnfant = $request->get('idEnfant');
        $idInscrit= $request->get('idInscrit');
        $em = $this->getDoctrine()->getManager();
        $club = $em->getRepository('KidzyBundle:Club')->find($idClub);
        $enfant = $em->getRepository('KidzyBundle:Enfant')->find($idEnfant);
        $inscrit = $em->getRepository('KidzyBundle:Inscription')->find($idInscrit);


        $html = $this->renderView('@Kidzy/club/print.html.twig', array(
            'enfant'  => $enfant,
            'club' => $club,
            'inscrit' => $inscrit
        ));
        $this->json(array ('title'=>'successful','message'=>"Attestation"),200);
        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'attestation.pdf'
        );

    }


}

