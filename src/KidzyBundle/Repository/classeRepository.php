<?php


namespace KidzyBundle\Repository;
use Doctrine\ORM\EntityRepository;



class classeRepository extends EntityRepository
{
    public function findclasse($id)
    {
        $qb=$this->getEntityManager()->createQuery("SELECT n FROM KidzyBundle:Classe n WHERE n.idClasse='".$id."'");
        $query=$qb->getArrayResult();
        return $query;

    }
    public function findenfant($id)
    {
        $qb=$this->getEntityManager()->createQuery("SELECT n.nomEnfant FROM KidzyBundle:Enfant n where n.idClasse='".$id."'");
        $query=$qb->getArrayResult();
        return $query;

    }
}