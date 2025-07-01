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

namespace Moloni\Api\Endpoints\Documents;

use Moloni\Api\Endpoints\Endpoint;
use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Estimate extends Endpoint
{
    /**
     * Gets estimate information
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryEstimate(?array $variables = []): array
    {
        $query = $this->loadQuery('estimate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Gets all estimates
     *
     * @param array|null $variables array variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function queryEstimates(?array $variables = []): array
    {
        $query = $this->loadQuery('estimates');

        return $this->paginatedPost($query, $variables, 'estimates');
    }

    /**
     * Get document token and path for estimates
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function queryEstimateGetPDFToken(?array $variables = []): array
    {
        $query = $this->loadQuery('estimateGetPDFToken');

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates an estimate
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationEstimateCreate(?array $variables = []): array
    {
        $query = $this->loadMutation('estimateCreate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Update an estimate
     *
     * @param array|null $variables variables of the request
     *
     * @return array Api data
     *
     * @throws MoloniApiException
     */
    public function mutationEstimateUpdate(?array $variables = []): array
    {
        $query = $this->loadMutation('estimateUpdate');

        return $this->simplePost($query, $variables);
    }

    /**
     * Creates estimate pdf
     *
     * @param array|null $variables
     *
     * @return array returns the Graphql response array or an error array
     *
     * @throws MoloniApiException
     */
    public function mutationEstimateGetPDF(?array $variables = []): array
    {
        $query = $this->loadMutation('estimateGetPDF');

        return $this->simplePost($query, $variables);
    }

    /**
     * Send estimate by mail
     *
     * @param array|null $variables
     *
     * @return array
     *
     * @throws MoloniApiException
     */
    public function mutationEstimateSendMail(?array $variables = []): array
    {
        $query = $this->loadMutation('estimateSendMail');

        return $this->simplePost($query, $variables);
    }
}
