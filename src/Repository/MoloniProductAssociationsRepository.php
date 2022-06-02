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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Moloni\Entity\MoloniProductAssociations;

class MoloniProductAssociationsRepository extends EntityRepository
{
    public function getAssociation($moloniId = null, $prestashopId = null, $combinationId = null): ?object
    {
        $props = [];

        if (!empty($moloniId)) {
            $props['mlProductId'] = $moloniId;
        }

        if (!empty($prestashopId)) {
            $props['psProductId'] = $prestashopId;
        }

        if (!empty($combinationId)) {
            $props['psCombinationId'] = $combinationId;
        }

        return $this->findOneBy($props);
    }

    public function addAssociation($moloniId, $prestashopId, $combinationId): void
    {
        $entityManager = $this->getEntityManager();

        $association = new MoloniProductAssociations();

        $association->setMlProductId($moloniId);
        $association->setPsProductId($prestashopId);
        $association->setPsCombinationId($combinationId);
        $association->setActive(1);

        try {
            $entityManager->persist($association);
            $entityManager->flush();
        } catch (OptimisticLockException|ORMException $e) {
            // catch this?
        }
    }

    public function deleteAssociation($moloniId): void
    {
        $entityManager = $this->getEntityManager();

        /** @var MoloniProductAssociations|null $object */
        $object = $this->getAssociation($moloniId);

        if ($object) {
            try {
                $entityManager->remove($object);
                $entityManager->flush();
            } catch (ORMException $e) {
                // catch this?
            }
        }
    }
}
