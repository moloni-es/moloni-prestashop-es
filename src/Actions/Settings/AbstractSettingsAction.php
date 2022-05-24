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

namespace Moloni\Actions\Settings;

use Store;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\Languages;
use Moloni\Exceptions\MoloniApiException;
use Symfony\Component\Form\FormFactory;

abstract class AbstractSettingsAction
{
    protected $languageId;
    protected $formBuilder;

    public function __construct(int $languageId, FormFactory $formBuilder)
    {
        $this->languageId = $languageId;
        $this->formBuilder = $formBuilder;
    }

    /**
     * Fetch required data for settings form
     *
     * @throws MoloniApiException
     */
    protected function getRequiredFormData(): array
    {
        $measurementUnits = $warehouses = $documentSets = $countries = $stores = [];

        $measurementUnitsQuery = MoloniApiClient::measurementUnits()->queryMeasurementUnits();
        $warehousesQuery = MoloniApiClient::warehouses()->queryWarehouses();
        $documentSetsQuery = MoloniApiClient::documentSets()->queryDocumentSets();
        $countriesQuery = MoloniApiClient::countries()->queryCountries([
            'options' => [
                'defaultLanguageId' => Languages::ES
            ]
        ]);
        $storesQuery = Store::getStores($this->languageId);

        foreach ($countriesQuery as $country) {
            $countries[$country['title']] = $country['countryId'];
        }

        foreach ($measurementUnitsQuery as $measurementUnit) {
            $measurementUnits[$measurementUnit['name']] = $measurementUnit['measurementUnitId'];
        }

        foreach ($warehousesQuery as $warehouse) {
            $warehouses[$warehouse['name']] = $warehouse['warehouseId'];
        }

        foreach ($documentSetsQuery as $documentSet) {
            $documentSets[$documentSet['name']] = $documentSet['documentSetId'];
        }

        foreach ($storesQuery as $store) {
            $stores[$store['name']] = $store['id_store'];
        }

        return [
            'measurementUnits' => $measurementUnits,
            'warehouses' => $warehouses,
            'documentSets' => $documentSets,
            'countries' => $countries,
            'stores' => $stores,
        ];
    }
}
