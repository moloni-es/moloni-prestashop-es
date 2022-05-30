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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class OrdersRepository
{
    /**
     * @var Connection the Database connection
     */
    private $connection;

    /**
     * @var string the Database prefix
     */
    private $databasePrefix;

    public function __construct(Connection $connection, $databasePrefix)
    {
        $this->connection = $connection;
        $this->databasePrefix = $databasePrefix;
    }

    public function getPendingOrdersPaginated($page, $langId, $orderDateCreated, $orderStatus): array
    {
        $expr = $this->connection->getExpressionBuilder();

        $ordersPerPage = 10;
        $offset = ($page - 1) * $ordersPerPage;
        $paginatorQuery = $this
            ->connection
            ->createQueryBuilder()
            ->addSelect('COUNT(*)')
            ->from($this->databasePrefix . 'orders', 'oo')
            ->leftJoin('oo', $this->databasePrefix . 'moloni_documents', 'mmdd', 'oo.id_order = mmdd.order_id')
            ->where('mmdd.id is null');

        if (!empty($orderDateCreated)) {
            $paginatorQuery
                ->andWhere('oo.date_add > :date_created')
                ->setParameter('date_created', $orderDateCreated);
        }

        if (!empty($orderStatus)) {
            $paginatorQuery->andWhere($expr->in('oo.current_state', $orderStatus));
        }

        $orders = $paginatorQuery->execute()
            ->fetch()['COUNT(*)'];

        $numberOfPages = ceil($orders / $ordersPerPage);
        $numberOfPages = $numberOfPages <= 0 ? 1 : $numberOfPages;

        $ordersQuery = $this
            ->connection
            ->createQueryBuilder()
            ->addSelect('o.id_order, o.reference,  o.date_add, o.id_customer, o.id_currency, o.total_paid_tax_incl, o.current_state, o.current_state')
            ->addSelect('c.email, c.firstname, c.lastname')
            ->addSelect('osl.name as state_name')
            ->addSelect('md.document_id as document_id')
            ->from($this->databasePrefix . 'orders', 'o')
            ->leftJoin('o', $this->databasePrefix . 'moloni_documents', 'md', 'o.id_order = md.order_id')
            ->leftJoin('o', $this->databasePrefix . 'customer', 'c', 'o.id_customer = c.id_customer')
            ->leftJoin('o', $this->databasePrefix . 'order_state_lang', 'osl', 'o.current_state = osl.id_order_state AND osl.id_lang = :languague_id')
            ->setParameter('languague_id', $langId)
            ->where($expr->isNull('document_id'));

        if (!empty($orderDateCreated)) {
            $ordersQuery
                ->andWhere('o.date_add > :date_created')
                ->setParameter('date_created', $orderDateCreated);
        }

        if (!empty($orderStatus)) {
            $ordersQuery->andWhere($expr->in('o.current_state', $orderStatus));
        }

        $ordersQuery = $ordersQuery
            ->setMaxResults($ordersPerPage)
            ->setFirstResult($offset)
            ->addOrderBy('o.date_add', 'DESC');

        return [
            'orders' => $ordersQuery->execute()->fetchAll(),
            'paginator' => [
                'numberOfPages' => $numberOfPages,
                'currentPage' => $page,
                'linesPerPage' => $ordersPerPage,
                'offset' => $offset,
            ],
        ];
    }
}
