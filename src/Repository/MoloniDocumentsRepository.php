<?php

namespace Moloni\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MoloniDocumentsRepository extends EntityRepository
{
    public function getAllPaginated(?int $currentPage = 1): array
    {
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->orderBy('id', 'DESC')
            ->getQuery();

        $pageSize = 10;
        $documents = [];

        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $offset = $pageSize * ($currentPage - 1);
        $paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($pageSize);

        foreach ($paginator as $document) {
            $documents[] = $document->toArray();
        }

        return [
            'documents' => $documents,
            'paginator' => [
                'numberOfTabs' => ceil($totalItems / $pageSize),
                'currentPage' => $currentPage,
                'linesPerPage' => $pageSize,
                'offset' => $offset,
            ],
        ];
    }
}
