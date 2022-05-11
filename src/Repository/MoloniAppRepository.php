<?php

namespace Moloni\Repository;

use Doctrine\ORM\EntityRepository;
use Moloni\Entity\MoloniApp;

class MoloniAppRepository extends EntityRepository
{
    public function getApp(): ?object
    {
        return $this
            ->findOneBy([]);
    }

    public function deleteApp()
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
