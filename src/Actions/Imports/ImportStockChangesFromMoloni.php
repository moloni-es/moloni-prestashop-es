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

namespace Moloni\Actions\Imports;

use Moloni\Api\MoloniApiClient;
use Moloni\Builders\PrestashopProductSimple;
use Moloni\Builders\PrestashopProductWithCombinations;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\Product\MoloniProductException;

class ImportStockChangesFromMoloni extends ImportProducts
{
    public function handle(): void
    {
        $props = [
            'options' => [
                'order' => [
                    'field' => 'reference',
                    'sort' => 'DESC',
                ],
                'filter' => [
                    'field' => 'hasStock',
                    'comparison' => 'eq',
                    'value' => 'true',
                ],
                'pagination' => [
                    'page' => $this->page,
                    'qty' => $this->itemsPerPage,
                ]
            ]
        ];


        try {
            $query = MoloniApiClient::products()->queryProducts($props, true);
        } catch (MoloniApiException $e) {
            return;
        }

        $this->totalResults = (int)($query['data']['products']['options']['count'] ?? 0);

        $data = $query['data']['products']['data'] ?? [];

        foreach ($data as $product) {
            try {
                if (empty($product['variants'])) {
                    $builder = new PrestashopProductSimple($product);
                } else {
                    $builder = new PrestashopProductWithCombinations($product);
                }

                $builder->updateStock();

                $this->syncedProducts[] = $product['reference'];
            } catch (MoloniProductException $e) {
                $this->errorProducts[] = [
                    $product['reference'] => [
                        'errorDump' => $e->getData()
                    ]
                ];
            }
        }

        sleep(3);
    }
}
