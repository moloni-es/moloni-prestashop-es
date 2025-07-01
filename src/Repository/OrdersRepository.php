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

namespace Moloni\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

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

    public function getPendingOrdersPaginated($page, $langId, $filters): array
    {
        $expr = $this->connection->getExpressionBuilder();

        $ordersPerPage = 10;
        $offset = ($page - 1) * $ordersPerPage;

        $paginatorQuery = $this->connection->createQueryBuilder();
        $paginatorQuery = $paginatorQuery
            ->addSelect('COUNT(*)')
            ->from($this->databasePrefix . 'orders', 'o')
            ->leftJoin('o', $this->databasePrefix . 'moloni_order_documents', 'md', 'o.id_order = md.order_id')
            ->leftJoin('o', $this->databasePrefix . 'customer', 'c', 'o.id_customer = c.id_customer')
            ->where('md.id is null');

        $this->applyFilters($paginatorQuery, $filters);

        $orders = $paginatorQuery
            ->execute()
            ->fetch()['COUNT(*)'];

        $numberOfPages = ceil($orders / $ordersPerPage);
        $numberOfPages = $numberOfPages <= 0 ? 1 : $numberOfPages;

        $ordersQuery = $this->connection->createQueryBuilder();
        $ordersQuery = $ordersQuery
            ->addSelect('o.id_order, o.reference,  o.date_add, o.id_customer, o.id_currency, o.total_paid_tax_incl, o.current_state, o.current_state')
            ->addSelect('c.email, c.firstname, c.lastname')
            ->addSelect('osl.name as state_name')
            ->addSelect('md.document_id as document_id')
            ->from($this->databasePrefix . 'orders', 'o')
            ->leftJoin('o', $this->databasePrefix . 'moloni_order_documents', 'md', 'o.id_order = md.order_id')
            ->leftJoin('o', $this->databasePrefix . 'customer', 'c', 'o.id_customer = c.id_customer')
            ->leftJoin('o', $this->databasePrefix . 'order_state_lang', 'osl', 'o.current_state = osl.id_order_state AND osl.id_lang = :languague_id')
            ->setParameter('languague_id', $langId)
            ->where($expr->isNull('document_id'));

        $this->applyFilters($ordersQuery, $filters);

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

    private function applyFilters(QueryBuilder $query, array $filters): void
    {
        $expr = $this->connection->getExpressionBuilder();

        if (!empty($filters['created_since'])) {
            $query
                ->andWhere('DATE(o.date_add) > :created_since')
                ->setParameter('created_since', $filters['created_since']);
        } elseif (!empty($filters['created_date'])) {
            $query
                ->andWhere('DATE(o.date_add) = :date_created')
                ->setParameter('date_created', $filters['created_date']);
        }

        if (!empty($filters['order_state'])) {
            $query->andWhere($expr->in('o.current_state', $filters['order_state']));
        }

        if (!empty($filters['order_reference'])) {
            $query
                ->andWhere($expr->like('o.reference', ':order_reference'))
                ->setParameter('order_reference', '%' . $filters['order_reference'] . '%');
        }

        if (!empty($filters['customer_email'])) {
            $query
                ->andWhere($expr->like('c.email', ':customer_email'))
                ->setParameter('customer_email', '%' . $filters['customer_email'] . '%');
        }

        if (!empty($filters['customer_name'])) {
            $query
                ->andWhere($expr->like('c.firstname', ':customer_name'))
                ->setParameter('customer_name', '%' . $filters['customer_name'] . '%');
        }
    }
}
