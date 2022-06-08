<?php

namespace Moloni\Form;

use DateTime;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\Boolean;
use Moloni\Enums\DocumentStatus;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\FiscalZone;
use Moloni\Enums\Languages;
use Moloni\Enums\LoadAddress;
use Moloni\Enums\ProductInformation;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Repository\MoloniSettingsRepository;
use Store;
use OrderState;
use Shop;
use Moloni\Enums\SyncFields;
use Moloni\Helpers\Settings;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SettingsFormDataProvider implements FormDataProviderInterface
{
    private $translator;
    private $languageId;

    private $settingsRepository;

    private $measurementUnits = [];
    private $stores = [];
    private $warehouses = [];
    private $documentSets = [];
    private $countries = [];
    private $orderStatus = [];
    private $yesNo = [];
    private $productInformation;
    private $status;
    private $documentTypes = [];
    private $fiscalZoneBasedOn = [];
    private $addresses = [];

    public function __construct(
        TranslatorInterface $translator,
        MoloniSettingsRepository $settingsRepository,
        $languageId
    ) {
        $this->translator = $translator;
        $this->languageId = $languageId;
        $this->settingsRepository = $settingsRepository;
    }

    public function getData(): array
    {
        $settings = Settings::getAll();

        if (isset($settings['orderDateCreated'])) {
            $settings['orderDateCreated'] = new DateTime($settings['orderDateCreated']);
        }

        if (!isset($settings['orderStatusToShow'])) {
            $settings['orderStatusToShow'] = $this->getPaidStatusIds();
        }

        if (!isset($settings['productSyncFields'])) {
            $settings['productSyncFields'] = SyncFields::getDefaultFields();
        }

        return $settings;
    }

    public function setData(array $data): array
    {
        $shopId = (int)Shop::getContextShopID();
        $this->settingsRepository->saveSettings($data, $shopId);

        return $data;
    }

    private function getPaidStatusIds(): array
    {
        $ids = [];

        $states = OrderState::getOrderStates(1);

        foreach ($states as $state) {
            if ((int)$state['paid'] === 1) {
                $ids[] = (int)$state['id_order_state'];
            }
        }

        return $ids;
    }

    /**
     * Fetch required data for settings form
     *
     * @return $this
     *
     * @throws MoloniApiException
     */
    public function loadMoloniAvailableSettings(): SettingsFormDataProvider
    {
        $measurementUnitsQuery = MoloniApiClient::measurementUnits()->queryMeasurementUnits();
        $warehousesQuery = MoloniApiClient::warehouses()->queryWarehouses();
        $documentSetsQuery = MoloniApiClient::documentSets()->queryDocumentSets();
        $countriesQuery = MoloniApiClient::countries()->queryCountries([
            'options' => [
                'defaultLanguageId' => Languages::ES
            ]
        ]);
        $storesQuery = Store::getStores($this->languageId);
        $orderStatusQuery = OrderState::getOrderStates($this->languageId);

        foreach ($orderStatusQuery as $states) {
            $this->orderStatus[$states['name']] = $states['id_order_state'];
        }

        foreach ($countriesQuery as $country) {
            $this->countries[$country['title']] = $country['countryId'];
        }

        foreach ($measurementUnitsQuery as $measurementUnit) {
            $this->measurementUnits[$measurementUnit['name']] = $measurementUnit['measurementUnitId'];
        }

        foreach ($warehousesQuery as $warehouse) {
            $this->warehouses[$warehouse['name']] = $warehouse['warehouseId'];
        }

        foreach ($documentSetsQuery as $documentSet) {
            $this->documentSets[$documentSet['name']] = $documentSet['documentSetId'];
        }

        foreach ($storesQuery as $store) {
            $this->stores[$store['name']] = $store['id_store'];
        }

        $this->yesNo = [
            $this->trans('No') => Boolean::NO,
            $this->trans('Yes') => Boolean::YES,
        ];

        $this->productInformation = [
            $this->trans('Prestashop') => ProductInformation::PRESTASHOP,
            $this->trans('Moloni') => ProductInformation::MOLONI,
        ];

        $this->status = [
            $this->trans('Draft') => DocumentStatus::DRAFT,
            $this->trans('Closed') => DocumentStatus::CLOSED,
        ];

        $this->documentTypes = DocumentTypes::getDocumentsTypes();

        $this->fiscalZoneBasedOn = [
            $this->trans('Billing') => FiscalZone::BILLING,
            $this->trans('Shipping') => FiscalZone::SHIPPING,
            $this->trans('Company') => FiscalZone::COMPANY,
        ];

        $this->addresses = [
            'Moloni company' => LoadAddress::MOLONI,
            'Custom' => LoadAddress::CUSTOM,
        ];

        if (!empty($this->stores)) {
            $this->addresses['Stores'] = $this->stores;
        }

        return $this;
    }

    /**
     * Simple translator implementation
     *
     * @param string $string
     *
     * @return string
     */
    private function trans(string $string): string
    {
        return $this->translator->trans($string, [], 'Modules.Molonies.Settings');
    }

    /**
     * @return array
     */
    public function getMeasurementUnits(): array
    {
        return $this->measurementUnits;
    }

    /**
     * @return array
     */
    public function getWarehouses(): array
    {
        return $this->warehouses;
    }

    /**
     * @return array
     */
    public function getDocumentSets(): array
    {
        return $this->documentSets;
    }

    /**
     * @return array
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * @return array
     */
    public function getOrderStatus(): array
    {
        return $this->orderStatus;
    }

    /**
     * @return array
     */
    public function getYesNo(): array
    {
        return $this->yesNo;
    }

    /**
     * @return array
     */
    public function getProductInformation(): array
    {
        return $this->productInformation;
    }

    /**
     * @return array
     */
    public function getStatus(): array
    {
        return $this->status;
    }

    /**
     * @return string[]
     */
    public function getDocumentTypes(): array
    {
        return $this->documentTypes;
    }

    /**
     * @return array
     */
    public function getFiscalZoneBasedOn(): array
    {
        return $this->fiscalZoneBasedOn;
    }

    /**
     * @return array
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }

    /**
     * @return array
     */
    public function getStores(): array
    {
        return $this->stores;
    }
}
