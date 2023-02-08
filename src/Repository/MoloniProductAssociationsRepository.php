<?php
/**
 * 2022 - Moloni.com
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Moloni
 * @copyright Moloni
 * @license   https://creativecommons.org/licenses/by-nd/4.0/
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Moloni\Repository;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Moloni\Entity\MoloniProductAssociations;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MoloniProductAssociationsRepository extends EntityRepository
{
    public function addAssociation($mlProductId, $mlProductReference, $mlVariantId, $psProductId, $psProductReference, $psCombinationId, $psCombinationReference, $active): void
    {
        $entityManager = $this->getEntityManager();

        $association = new MoloniProductAssociations();
        $association->setMlProductId($mlProductId ?? 0);
        $association->setMlProductReference($mlProductReference ?? '');
        $association->setMlVariantId($mlVariantId ?? 0);
        $association->setPsProductId($psProductId ?? 0);
        $association->setPsProductReference($psProductReference ?? '');
        $association->setPsCombinationId($psCombinationId ?? 0);
        $association->setPsCombinationReference($psCombinationReference ?? '');
        $association->setActive($active ?? 1);

        try {
            $entityManager->persist($association);
            $entityManager->flush();
        } catch (OptimisticLockException|ORMException $e) {
            // catch this?
        }
    }

    public function deleteByMoloniId($moloniId): void
    {
        $this->createQueryBuilder('a')
            ->delete()
            ->where('a.mlProductId = :moloni_id')
            ->setParameter('moloni_id', $moloniId)
            ->getQuery()
            ->getResult();
    }

    public function deleteByPrestashopId($prestashopId): void
    {
        $this->createQueryBuilder('a')
            ->delete()
            ->where('a.psProductId = :prestashop_id')
            ->setParameter('prestashop_id', $prestashopId)
            ->getQuery()
            ->getResult();
    }

    public function deleteByCombinationId($combinationId): void
    {
        $this->createQueryBuilder('a')
            ->delete()
            ->where('a.psCombinationId = :combination_id')
            ->setParameter('combination_id', $combinationId)
            ->getQuery()
            ->getResult();
    }
}
