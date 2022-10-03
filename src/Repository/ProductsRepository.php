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

declare(strict_types=1);

namespace Moloni\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductsRepository
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

    public function getProductsPaginated($page, $langId, $shopId, $filters): array
    {
        $productsPerPage = 20;
        $offset = ($page - 1) * $productsPerPage;

        $paginatorQuery = $this->connection->createQueryBuilder();
        $paginatorQuery = $paginatorQuery
            ->addSelect('COUNT(*)')
            ->from($this->databasePrefix . 'product', 'p')
            ->leftJoin('p', $this->databasePrefix . 'product_shop', 'ps', 'p.id_product = ps.id_product')
            ->leftJoin('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->where('ps.id_shop = :shop_id')
            ->andWhere('ps.active = 1')
            ->andWhere('pl.id_lang = :languague_id')
            ->setParameter('shop_id', $shopId)
            ->setParameter('languague_id', $langId);

        $this->applyFilters($paginatorQuery, $filters);

        $orders = $paginatorQuery
            ->execute()
            ->fetch()['COUNT(*)'];

        $numberOfPages = ceil($orders / $productsPerPage);
        $numberOfPages = $numberOfPages <= 0 ? 1 : $numberOfPages;

        $productsQuery = $this->connection->createQueryBuilder();
        $productsQuery = $productsQuery
            ->addSelect('p.id_product, p.reference, pl.name')
            ->from($this->databasePrefix . 'product', 'p')
            ->leftJoin('p', $this->databasePrefix . 'product_shop', 'ps', 'p.id_product = ps.id_product')
            ->leftJoin('p', $this->databasePrefix . 'product_lang', 'pl', 'p.id_product = pl.id_product')
            ->where('ps.id_shop = :shop_id')
            ->andWhere('ps.active = 1')
            ->andWhere('pl.id_lang = :languague_id')
            ->setParameter('shop_id', $shopId)
            ->setParameter('languague_id', $langId);

        $this->applyFilters($productsQuery, $filters);

        $productsQuery = $productsQuery
            ->setMaxResults($productsPerPage)
            ->setFirstResult($offset)
            ->addOrderBy('p.id_product', 'DESC');

        return [
            'products' => $productsQuery->execute()->fetchAll(),
            'paginator' => [
                'numberOfPages' => $numberOfPages,
                'currentPage' => $page,
                'linesPerPage' => $productsPerPage,
                'offset' => $offset,
            ],
        ];
    }

    private function applyFilters(QueryBuilder $query, array $filters): void
    {
        $expr = $this->connection->getExpressionBuilder();

        if (!empty($filters['reference'])) {
            $query
                ->andWhere($expr->like('p.reference', ':reference'))
                ->setParameter('reference', '%' . $filters['reference'] . '%');
        }

        if (!empty($filters['name'])) {
            $query
                ->andWhere($expr->like('pl.name', ':name'))
                ->setParameter('name', '%' . $filters['name'] . '%');
        }
    }
}
