<?php

namespace Moloni\Api\Endpoints;

use Moloni\Api\MoloniApi;
use Moloni\Exceptions\MoloniApiException;

abstract class Endpoint
{
    /**
     * Requests cache
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Make a simple request
     *
     * @param string $query
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    protected function simplePost(string $query, ?array $variables): array
    {
        return MoloniApi::post([
           'query' => $query,
           'variables' => $variables,
        ]);
    }

    /**
     * Make post with file upload
     *
     * @param array $operations
     * @param string|null $map
     * @param string|null $file
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    protected function postWithFile(array $operations = [], ?string $map = '', ?string $file = ''): array
    {
        return MoloniApi::postWithFile($operations, $map, $file);
    }

    /**
     * Make a paginated request
     *
     * @param string $query
     * @param array|null $variables
     * @param string $key
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    protected function paginatedPost(string $query, ?array $variables, string $key): array
    {
        $pageNumber = 0;
        $pageLimit = 100;
        $data = [];

        do {
            ++$pageNumber;

            $variables['options']['pagination']['qty'] = 50;
            $variables['options']['pagination']['page'] = $pageNumber;

            $queryResult = MoloniApi::post([
                'query' => $query,
                'variables' => $variables,
            ]);

            if (isset($queryResult['errors'])) {
                throw new MoloniApiException('Error paginating request', [], ['query' => $query, 'variables' => $variables, 'result' => $queryResult]);
            }

            $querySize = $queryResult['data'][$key]['options']['pagination'];

            /** @noinspection SlowArrayOperationsInLoopInspection */
            $data = array_merge($data, $queryResult['data'][$key]['data']);
        } while ($querySize['count'] > ($querySize['qty'] * $querySize['page']) && $pageNumber < $pageLimit);

        return $data;
    }
}
