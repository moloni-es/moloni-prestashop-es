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

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Moloni\Entity\MoloniDocuments;

class MoloniDocumentsRepository extends EntityRepository
{
    /**
     * Paginated documents created by plugin
     *
     * @throws Exception
     */
    public function getAllPaginated(?int $page = 1, ?array $filters = []): array
    {
        $documents = [];
        $documentsPerPage = 10;

        $query = $this->createQueryBuilder('md');

        $this->applyFilters($query, $filters);

        $query->orderBy('md.id', 'DESC');

        $paginator = new Paginator($query->getQuery(), false);

        $totalItems = $paginator->count();
        $totalItems = $totalItems === 0 ? 1 : $totalItems;

        $numberOfPages = ceil($totalItems / $documentsPerPage);
        $numberOfPages = $numberOfPages <= 0 ? 1 : $numberOfPages;

        $offset = ($page - 1) * $documentsPerPage;

        $paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($documentsPerPage);

        /** @var MoloniDocuments[] $objects */
        $documentsObjects = $paginator
            ->getIterator()
            ->getArrayCopy();

        /** @var MoloniDocuments $object */
        foreach ($documentsObjects as $object) {
            $documents[] = $object->toArray();
        }

        return [
            'documents' => $documents,
            'paginator' => [
                'numberOfPages' => $numberOfPages,
                'currentPage' => $page,
                'linesPerPage' => $documentsPerPage,
                'offset' => $offset,
            ],
        ];
    }

    private function applyFilters(QueryBuilder $query, array $filters): void
    {
        if (!empty($filters['created_date'])) {
            try {
                $from = new DateTime($filters['created_date'] . " 00:00:00");
                $to = new DateTime($filters['created_date'] . " 23:59:59");
                $query
                    ->andWhere('md.createdAt BETWEEN :from AND :to')
                    ->setParameter('from', $from)
                    ->setParameter('to', $to);
            } catch (Exception $e) {
                // catch nothing
            }
        }

        if (!empty($filters['document_type'])) {
            $query
                ->andWhere('md.documentType = :document_type')
                ->setParameter('document_type', $filters['document_type']);
        }

        if (!empty($filters['order_reference'])) {
            $query
                ->andWhere('md.orderReference LIKE :order_reference')
                ->setParameter('order_reference', '%' . $filters['order_reference'] . '%');
        }
    }
}
