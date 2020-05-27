<?php


namespace KidzyBundle\Repository;


use Doctrine\ORM\EntityRepository;


class reclamationsRepository extends EntityRepository
{
    public function myfindrec()
    {
        $qb = $this->getEntityManager()->createQuery(
            "select  c.etatRec etatRec , count(c.idRec) NB from KidzyBundle:Reclamations c group by c.etatRec");

        return $query = $qb->getResult();

    }


    public function mylistRec($idParent)
    {
        $qb = $this->getEntityManager()->createQuery(
            "select r.idRec ,  r.dateRec , r.descriptionRec 
             from KidzyBundle:Reclamations r 
             WHERE  r.id=:id")
            ->setParameter('id', $idParent);
        return $query = $qb->getResult();

    }


}