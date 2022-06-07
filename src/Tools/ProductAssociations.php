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

namespace Moloni\Tools;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ObjectRepository;
use Moloni\Entity\MoloniProductAssociations;
use Moloni\Repository\MoloniProductAssociationsRepository;

class ProductAssociations
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * Associations repository
     *
     * @var EntityRepository|ObjectRepository|MoloniProductAssociationsRepository
     */
    private static $associationManager;

    /**
     * Construct
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        self::$entityManager = $entityManager;
        self::$associationManager = $entityManager->getRepository(MoloniProductAssociations::class);
    }

    public static function getByMoloniId($id): ?object
    {
        return self::$associationManager->getAssociation($id);
    }

    public static function getByPrestaId($id): ?object
    {
        return self::$associationManager->getAssociation(null, $id);
    }

    public static function addAssociation(): void
    {
        $association = new MoloniProductAssociations();
        // todo: finish this

        try {
            self::$entityManager->persist($association);
            self::$entityManager->flush();
        } catch (ORMException $e) {
            // do not catch
        }
    }

    public static function deleteAssociation(): void
    {
        // todo: finish this
    }
}
