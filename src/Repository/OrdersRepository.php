<?php

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

    public function getPendingOrdersPaginated($page, $langId, $dateCreated): array
    {
        $expr = $this->connection->getExpressionBuilder();

        $ordersPerPage = 5;
        $offset = ($page - 1) * $ordersPerPage;
        $orders = $this
            ->connection
            ->createQueryBuilder()
            ->addSelect('COUNT(*)')
            ->from($this->databasePrefix . 'orders', 'oo')
            ->leftJoin('oo', $this->databasePrefix . 'moloni_documents', 'mmdd', 'oo.id_order = mmdd.order_id')
            ->where('mmdd.id is null')
            ->execute()
            ->fetch()['COUNT(*)'];
        $numberOfPages = ceil($orders / $ordersPerPage);

        $ordersQuery = $this
            ->connection
            ->createQueryBuilder()
            ->addSelect('o.id_order, o.reference,  o.date_add, o.id_customer, o.id_currency, o.total_paid_tax_incl,o.current_state')
            ->addSelect('c.email, c.firstname, c.lastname')
            ->addSelect('osl.name as state_name')
            ->addSelect('md.document_id as document_id')
            ->from($this->databasePrefix . 'orders', 'o')
            ->leftJoin('o', $this->databasePrefix . 'moloni_documents', 'md', 'o.id_order = md.order_id')
            ->leftJoin('o', $this->databasePrefix . 'customer', 'c', 'o.id_customer = c.id_customer')
            ->leftJoin('o', $this->databasePrefix . 'order_state_lang', 'osl', 'o.current_state = osl.id_order_state AND osl.id_lang = :languague_id')
            ->setParameter('languague_id', $langId);

        if (!empty($dateCreated)) {
            $ordersQuery
                ->where('o.date_add > :date_created')
                ->setParameter('date_created', $dateCreated);
        }

        $ordersQuery = $ordersQuery
            ->where($expr->isNull('document_id'))
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
