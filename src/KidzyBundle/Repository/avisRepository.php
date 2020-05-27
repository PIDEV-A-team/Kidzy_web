<?php


namespace KidzyBundle\Repository;


use Doctrine\ORM\EntityRepository;


class avisRepository extends EntityRepository
{
    public function mylistAvis($idParent)
    {
        $qb = $this->getEntityManager()->createQuery(
            "select a.idAvis ,  a.dateAvis , a.descriptionAvis 
             from KidzyBundle:Avis a 
             WHERE  a.id=:id")
            ->setParameter('id', $idParent);
        return $query = $qb->getResult();

    }


}