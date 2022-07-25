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

namespace Moloni\Api\Endpoints;

use Moloni\Exceptions\MoloniApiException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Registration extends Endpoint
{
    /**
     * Add new registration
     *
     * @throws MoloniApiException
     */
    public function mutationSignUpCompany(?array $variables = []): array
    {
        $query = 'mutation signUpCompany($data: CompanySignUpInput!)
        {
            signUpCompany(data: $data)
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Check if slug is free
     *
     * @throws MoloniApiException
     */
    public function queryGetFreeSlug(?array $variables = []): array
    {
        $query = 'query getFreeSlug($slug: String!)
        {
            getFreeSlug(slug: $slug)
        }';

        return $this->simplePost($query, $variables);
    }

    /**
     * Check if VAT is free
     *
     * @throws MoloniApiException
     */
    public function queryGetFreeVAT(?array $variables = []): array
    {
        $query = 'query getFreeVAT($vat: String!)
        {
            getFreeVAT(vat: $vat)
        }';

        return $this->simplePost($query, $variables);
    }
}
