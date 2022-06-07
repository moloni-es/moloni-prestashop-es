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

namespace Moloni\Builders\MoloniProduct\Helpers;

use Moloni\Api\MoloniApiClient;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Helpers\Logs;

class UpdateMoloniProductStock
{
    private $moloniProductId;
    private $moloniProductWarehouses;
    private $reference;
    private $warehouseId;
    private $newStock;

    /**
     * Construct
     *
     * @param int $moloniProductId
     * @param int $warehouseId
     * @param float|int|null $newStock
     * @param array $moloniProductWarehouses
     * @param string $reference
     *
     * @throws MoloniApiException
     */
    public function __construct(int $moloniProductId, int $warehouseId, $newStock, array $moloniProductWarehouses, string $reference)
    {
        $this->moloniProductId = $moloniProductId;
        $this->warehouseId = $warehouseId;
        $this->newStock = $newStock;

        $this->moloniProductWarehouses = $moloniProductWarehouses;
        $this->reference = $reference;

        $this->handle();
    }

    /**
     * Handler
     *
     * @throws MoloniApiException
     */
    private function handle(): void
    {
        $moloniStock = 0;

        foreach ($this->moloniProductWarehouses as $warehouse) {
            if ($warehouse['warehouseId'] === $this->warehouseId) {
                $moloniStock = $warehouse['stock'];

                break;
            }
        }

        if ($moloniStock === $this->newStock) {
            Logs::addInfoLog(['Stock is already updated in Moloni ({0})', ['{0}' => $this->reference]]);

            return;
        }

        $props = [
            'productId' => $this->moloniProductId,
            'notes' => 'Prestashop',
            'warehouseId' => $this->warehouseId,
        ];

        if ($moloniStock > $this->newStock) {
            $diference = $moloniStock - $this->newStock;

            $props['qty'] = $diference;

            $mutation = MoloniApiClient::stock()->mutationStockMovementManualExitCreate(['data' => $props]);
        } else {
            $diference = $this->newStock - $moloniStock;

            $props['qty'] = $diference;

            $mutation = MoloniApiClient::stock()->mutationStockMovementManualEntryCreate(['data' => $props]);
        }

        $message = [
            'Stock updated in Moloni (old: {0} | new: {1}) ({2})', [
                '{0}' => $moloniStock,
                '{1}' => $this->newStock,
                '{2}' => $this->reference,
            ]
        ];

        Logs::addInfoLog($message, ['mutation' => $mutation]);
    }
}
