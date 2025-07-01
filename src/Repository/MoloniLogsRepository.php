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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Moloni\Entity\MoloniSyncLogs;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MoloniLogsRepository extends EntityRepository
{
    /**
     * @throws \Exception
     */
    public function getAllPaginated(?int $page = 1, ?array $filters = []): array
    {
        $logs = [];
        $logsPerPage = 10;

        $query = $this->createQueryBuilder('l');

        $this->applyFilters($query, $filters);

        $query->orderBy('l.id', 'DESC');

        $paginator = new Paginator($query->getQuery(), false);

        $totalItems = $paginator->count();
        $totalItems = $totalItems === 0 ? 1 : $totalItems;

        $numberOfPages = ceil($totalItems / $logsPerPage);
        $numberOfPages = $numberOfPages <= 0 ? 1 : $numberOfPages;

        $offset = ($page - 1) * $logsPerPage;

        $paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($logsPerPage);

        /** @var MoloniSyncLogs[] $objects */
        $logsObjects = $paginator
            ->getIterator()
            ->getArrayCopy();

        /** @var MoloniSyncLogs $object */
        foreach ($logsObjects as $object) {
            $logs[] = $object->toArray();
        }

        return [
            'logs' => $logs,
            'paginator' => [
                'numberOfPages' => $numberOfPages,
                'currentPage' => $page,
                'linesPerPage' => $logsPerPage,
                'offset' => $offset,
            ],
        ];
    }

    /**
     * Delete logs with more than 1 week
     */
    public function deleteOlderLogs(): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->where('l.createdAt < :created_at')
            ->setParameter('created_at', new \DateTime('@' . strtotime('-1 week')))
            ->getQuery()
            ->getResult();
    }

    /**
     * Apply list query filters
     *
     * @param QueryBuilder $query
     * @param array $filters
     *
     * @return void
     */
    private function applyFilters(QueryBuilder $query, array $filters): void
    {
        if (!empty($filters['company_id'])) {
            $query
                ->where('l.companyId = :company_id')
                ->setParameter('company_id', $filters['company_id']);
        }

        if (!empty($filters['created_date'])) {
            try {
                $from = new \DateTime($filters['created_date'] . ' 00:00:00');
                $to = new \DateTime($filters['created_date'] . ' 23:59:59');
                $query
                    ->andWhere('l.createdAt BETWEEN :from AND :to')
                    ->setParameter('from', $from)
                    ->setParameter('to', $to);
            } catch (\Exception $e) {
                // catch nothing
            }
        }

        if (!empty($filters['log_level'])) {
            $query
                ->andWhere('l.level = :log_level')
                ->setParameter('log_level', $filters['log_level']);
        }

        if (!empty($filters['message_text'])) {
            $query
                ->andWhere('l.message LIKE :message_text')
                ->setParameter('message_text', '%' . $filters['message_text'] . '%');
        }
    }
}
