<?php

/**
 * 2025 - Moloni.com
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

use Doctrine\ORM\EntityManagerInterface;
use Moloni\Entity\MoloniProductAssociations;
use Moloni\MoloniContext;
use Moloni\Repository\MoloniProductAssociationsRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductAssociations
{
    /**
     * Associations repository
     *
     * @var MoloniProductAssociationsRepository
     */
    private static $associationRepository;

    /**
     * Moloni context
     *
     * @var MoloniContext
     */
    protected static $context;

    /**
     * Construct
     *
     * @param EntityManagerInterface $entityManager
     * @param MoloniContext $context
     */
    public function __construct(EntityManagerInterface $entityManager, MoloniContext $context)
    {
        /** @var MoloniProductAssociationsRepository $repository */
        $repository = $entityManager->getRepository(MoloniProductAssociations::class);

        self::$associationRepository = $repository;
        self::$context = $context;
    }

    //          RETRIEVES          //

    public static function findAll(): array
    {
        return self::$associationRepository->findAll();
    }

    public static function findByMoloniParentId($parentId): array
    {
        return self::$associationRepository->findBy(['mlProductId' => $parentId]);
    }

    public static function findByMoloniVariantId($variantId): ?object
    {
        return self::$associationRepository->findOneBy(['mlVariantId' => $variantId], ['id' => 'DESC']);
    }

    public static function findByPrestashopProductId($productId): array
    {
        return self::$associationRepository->findBy(['psProductId' => $productId]);
    }

    public static function findByPrestashopCombinationId($combinationId): ?object
    {
        return self::$associationRepository->findOneBy(['psCombinationId' => $combinationId], ['id' => 'DESC']);
    }

    //          CRUD          //

    public static function add($mlProductId, $mlProductReference, $mlVariantId, $psProductId, $psProductReference, $psCombinationId, $psCombinationReference, $active): void
    {
        self::$associationRepository->addAssociation($mlProductId, $mlProductReference, $mlVariantId, $psProductId, $psProductReference, $psCombinationId, $psCombinationReference, $active);
    }

    public static function deleteByPrestashopId($prestashopId): void
    {
        self::$associationRepository->deleteByPrestashopId($prestashopId);
    }

    public static function deleteByCombinationId($combinationId): void
    {
        self::$associationRepository->deleteByCombinationId($combinationId);
    }

    public static function deleteByMoloniId($moloniId): void
    {
        self::$associationRepository->deleteByMoloniId($moloniId);
    }
}
