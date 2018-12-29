<?php

namespace OC\PlatformBundle\Repository;

/**
 * ApplicationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ApplicationRepository extends \Doctrine\ORM\EntityRepository
{
    public function getApplicationsWithAdvert($limit)
    {
        $qb = $this->createQueryBuilder('application');

        $qb->innerJoin('application.advert', 'advert')
           ->addSelect('advert');

        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResults();
    }
}