<?php


namespace KidzyBundle\Repository;
use UserBundle\Entity\User;

use Doctrine\ORM\EntityRepository;


class userRepository extends EntityRepository
{
    public function ajoutMaitresse()
    {
        $qb = $this->getEntityManager()->createQuery(

            "select u.nom , u.prenom  from UserBundle:User u where u.roles='a:1:{i:0;s:14:\"ROLE_MAITRESSE\";}' ");

        return $query = $qb->getResult();

    }

}