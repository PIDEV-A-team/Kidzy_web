<?php


namespace KidzyBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Tests\OrmFunctionalTestCase;

class enfantRepository extends EntityRepository
{
    public function myEnfant()
    {
        $qb=$this->getEntityManager()->createQuery("select e from KidzyBundle:Enfant e Where e.prenomEnfant = 'sonia' " );

        return $query = $qb->getResult();


    }
    public function search($prenomEnfant) {
        return $this->createQueryBuilder('Enfant')
            ->andWhere('Enfant.prenomEnfant LIKE :prenomEnfant')
            ->setParameter('prenomEnfant', '%'.$prenomEnfant.'%')
            ->getQuery()
            ->execute();
    }

    public function mylistEnfant($idParent)
    {
        $qb = $this->getEntityManager()->createQuery(
            "select e.idEnfant ,  (e.idClasse)  AS idClasse, (e.idGarde) AS idGarde ,e.nomEnfant , e.prenomEnfant , e.imageEnfant , e.datenEnfant ,  c.libelleCla , g.nomGarde  
             from KidzyBundle:Enfant e , KidzyBundle:Garde g , KidzyBundle:Classe c  
             WHERE  e.idClasse = c.idClasse AND g.idGarde = e.idGarde AND  e.idParent=:id")
            ->setParameter('id', $idParent);
        return $query = $qb->getResult();

    }
    public function myfindEnfant($idEnfant)
    {
        $qb = $this->getEntityManager()->createQuery(
            "select e.idEnfant ,e.nomEnfant , e.prenomEnfant , e.imageEnfant , e.datenEnfant , c.libelleCla , g.nomGarde , p.nom 
             from KidzyBundle:Enfant e , KidzyBundle:Garde g , KidzyBundle:Classe c , UserBundle:User p 
             WHERE e.idClasse = c.idClasse AND g.idGarde = e.idGarde AND p.id = e.idParent AND e.idEnfant =:id ")

            ->setParameter('id', $idEnfant);
        return $query=$qb->getResult();

    }
}