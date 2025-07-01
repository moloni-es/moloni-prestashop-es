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

namespace Moloni\Actions\Orders;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Moloni\Entity\MoloniOrderDocuments;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniOrderDocumentsRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class AbstractOrderAction
{
    /**
     * Order id
     *
     * @var int|null
     */
    public $orderId;

    /**
     * Order object
     *
     * @var \Order|null
     */
    public $order;

    /**
     * Entity manager
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Documents entity repository
     *
     * @var EntityRepository|MoloniOrderDocumentsRepository
     */
    protected $documentRepository;

    /**
     * Constructor
     *
     * @param int|string|null $orderId
     * @param EntityManagerInterface|ObjectManager $entityManager
     *
     * @throws MoloniException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function __construct($orderId, $entityManager)
    {
        if (!is_numeric($orderId) || $orderId < 0) {
            throw new MoloniException('ID is invalid');
        }

        $order = new \Order($orderId);

        if (empty($order->id)) {
            throw new MoloniException('Order does not exist!');
        }

        $this->order = $order;
        $this->orderId = $orderId;
        $this->entityManager = $entityManager;
        $this->documentRepository = $entityManager->getRepository(MoloniOrderDocuments::class);
    }

    //          Gets          //

    public function getOrder(): ?\Order
    {
        return $this->order;
    }
}
