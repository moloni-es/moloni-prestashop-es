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

namespace Moloni\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Moloni\Repository\MoloniSyncLogsRepository")
 */
class MoloniSyncLogs
{
    /**
     * @var int
     *
     * @ORM\Id
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="type_id", type="integer")
     */
    private $typeId;

    /**
     * @var int
     *
     * @ORM\Column(name="prestashop_id", type="integer")
     */
    private $prestashopId;

    /**
     * @var int
     *
     * @ORM\Column(name="moloni_id", type="integer")
     */
    private $moloniId;

    /**
     * @var int
     *
     * @ORM\Column(name="shop_id", type="integer")
     */
    private $shopId;

    /**
     * @var string
     *
     * @ORM\Column(name="sync_date", type="string", length=250)
     */
    private $syncDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return $this->typeId;
    }

    /**
     * @param int $typeId
     */
    public function setTypeId(int $typeId): void
    {
        $this->typeId = $typeId;
    }

    /**
     * @return int
     */
    public function getPrestashopId(): int
    {
        return $this->prestashopId;
    }

    /**
     * @param int $prestashopId
     */
    public function setPrestashopId(int $prestashopId): void
    {
        $this->prestashopId = $prestashopId;
    }

    /**
     * @return int
     */
    public function getMoloniId(): int
    {
        return $this->moloniId;
    }

    /**
     * @param int $moloniId
     */
    public function setMoloniId(int $moloniId): void
    {
        $this->moloniId = $moloniId;
    }

    /**
     * @return string
     */
    public function getSyncDate(): string
    {
        return $this->syncDate;
    }

    /**
     * @param string $syncDate
     */
    public function setSyncDate(string $syncDate): void
    {
        $this->syncDate = $syncDate;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     */
    public function setShopId(int $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type_id' => $this->getTypeId(),
            'prestashop_id' => $this->getPrestashopId(),
            'moloni_id' => $this->getMoloniId(),
            'sync_date' => $this->getSyncDate(),
            'shop_id' => $this->getShopId(),
        ];
    }
}
