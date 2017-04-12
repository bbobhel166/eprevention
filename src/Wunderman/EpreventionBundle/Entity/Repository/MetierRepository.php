<?php
/**
 * Created by PhpStorm.
 * User: helvasb
 * Date: 28/01/2017
 * Time: 11:53
 */

namespace Wunderman\EpreventionBundle\Entity\Repository;

use Wunderman\EpreventionBundle\Entity\Metier;
use Doctrine\ORM\EntityRepository;


class MetierRepository extends EntityRepository
{
    public function findAllQueryBuilder($filter = '')
    {
        $qb = $this->createQueryBuilder('metiers');

        if ($filter) {
            $qb->andWhere('metiers.titre LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        return $qb;
    }

    /**
     * @param $nickname
     * @return Programmer
     */
    public function findOneById($id)
    {
        return $this->findOneBy(array('id' => $id));
    }
}