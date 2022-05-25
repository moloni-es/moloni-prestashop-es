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

use Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Moloni\Entity\MoloniSyncLogs;

class MoloniLogsRepository extends EntityRepository
{
    /**
     * @throws Exception
     */
    public function getAllPaginated(?int $page = 1, ?int $companyId = 0): array
    {
        $logs = [];
        $logsPerPage = 10;

        $query = $this
            ->createQueryBuilder('l')
            ->where('l.companyId = :company_id')
            ->setParameter('company_id', $companyId)
            ->orderBy('l.id', 'DESC')
            ->getQuery();

        $paginator = new Paginator($query, false);

        $totalItems = $paginator->count();
        $totalItems = $totalItems === 0 ? 1 : $totalItems;
        $numberOfPages = ceil($totalItems / $logsPerPage);
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
     * Delete logs with more than 1 month
     */
    public function deleteOlderLogs(): void
    {
        $timestampp = strtotime("-1 week");

        $this->createQueryBuilder('l')
            ->delete()
            ->where('l.createdAt < :created_at')
            ->setParameter('created_at', $timestampp)
            ->getQuery()
            ->getResult();
    }
}
