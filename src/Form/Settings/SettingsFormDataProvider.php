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

declare(strict_types=1);

namespace Moloni\Form\Settings;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Moloni\Api\MoloniApiClient;
use Moloni\Entity\MoloniSettings;
use Moloni\Enums\Boolean;
use Moloni\Enums\DocumentReference;
use Moloni\Enums\DocumentStatus;
use Moloni\Enums\DocumentTypes;
use Moloni\Enums\FiscalZone;
use Moloni\Enums\Languages;
use Moloni\Enums\LoadAddress;
use Moloni\Enums\ProductInformation;
use Moloni\Enums\SyncFields;
use Moloni\Exceptions\MoloniApiException;
use Moloni\MoloniContext;
use Moloni\Tools\Settings;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SettingsFormDataProvider implements FormDataProviderInterface
{
    private $translator;
    private $languageId;
    private $companyId;

    private $settingsRepository;

    private $measurementUnits = [];
    private $stores = [];
    private $warehouses = [];
    private $documentSets = [];
    private $documentReference = [];
    private $countries = [];
    private $orderStatus = [];
    private $yesNo = [];
    private $productInformation;
    private $status;
    private $documentTypes = [];
    private $fiscalZoneBasedOn = [];
    private $syncFields = [];
    private $addresses = [];
    private $customerLanguage = [];
    private $exemptionReasons = [];
    private $companyName = '';

    public function __construct(MoloniContext $context, int $languageId)
    {
        $this->translator = $context->iTranslator();
        $this->settingsRepository = $context->iEntityManager()->getRepository(MoloniSettings::class);

        $this->languageId = $languageId;
        $this->companyId = $context->getCompanyId();
    }

    public function getData(): array
    {
        $settings = Settings::getAll();

        if (isset($settings['orderDateCreated'])) {
            if (empty($settings['orderDateCreated'])) {
                $settings['orderDateCreated'] = null;
            } else {
                $settings['orderDateCreated'] = new \DateTime($settings['orderDateCreated']);
            }
        }

        if (!isset($settings['orderStatusToShow'])) {
            $settings['orderStatusToShow'] = $this->getPaidStatusIds();
        }

        if (!isset($settings['productSyncFields'])) {
            $settings['productSyncFields'] = SyncFields::getDefaultFields();
        }

        $settings['companyName'] = $this->getCompanyName();

        return $settings;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function setData(array $data): array
    {
        $shopId = (int) \Shop::getContextShopID();

        $this->settingsRepository->saveSettings($data, $shopId, $this->companyId);

        return $data;
    }

    private function getPaidStatusIds(): array
    {
        $ids = [];
        $languageId = (int) \Configuration::get('PS_LANG_DEFAULT');

        $states = \OrderState::getOrderStates($languageId);

        foreach ($states as $state) {
            if ((int) $state['paid'] === 1) {
                $ids[] = (int) $state['id_order_state'];
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
        $companyQuery = MoloniApiClient::companies()->queryCompany();
        $warehousesQuery = MoloniApiClient::warehouses()->queryWarehouses();
        $documentSetsQuery = MoloniApiClient::documentSets()->queryDocumentSets();
        $countriesQuery = MoloniApiClient::countries()->queryCountries([
            'options' => [
                'defaultLanguageId' => Languages::EN,
            ],
        ]);
        $storesQuery = \Store::getStores($this->languageId);
        $orderStatusQuery = \OrderState::getOrderStates($this->languageId);

        $this->companyName = $companyQuery['name'];

        foreach ($companyQuery['fiscalZone']['exemption']['reasons'] ?? [] as $reason) {
            $this->exemptionReasons["{$reason['code']} - {$reason['name']}"] = $reason['code'];
        }

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
            $this->trans('No', 'Modules.Molonies.Settings') => Boolean::NO,
            $this->trans('Yes', 'Modules.Molonies.Settings') => Boolean::YES,
        ];

        $this->productInformation = [
            'Prestashop' => ProductInformation::PRESTASHOP,
            'Moloni' => ProductInformation::MOLONI,
        ];

        $this->status = [
            $this->trans('Draft', 'Modules.Molonies.Settings') => DocumentStatus::DRAFT,
            $this->trans('Closed', 'Modules.Molonies.Settings') => DocumentStatus::CLOSED,
        ];

        foreach (DocumentTypes::getDocumentsTypes() as $name => $code) {
            $this->documentTypes[$this->trans($name, 'Modules.Molonies.Settings')] = $code;
        }

        foreach (SyncFields::getSyncFields() as $name => $code) {
            $this->syncFields[$this->trans($name, 'Modules.Molonies.Settings')] = $code;
        }

        $this->documentReference = [
            $this->trans('Order reference', 'Modules.Molonies.Settings') => DocumentReference::REFERENCE,
            $this->trans('Order ID', 'Modules.Molonies.Settings') => DocumentReference::ID,
        ];

        $this->fiscalZoneBasedOn = [
            $this->trans('Billing', 'Modules.Molonies.Settings') => FiscalZone::BILLING,
            $this->trans('Shipping', 'Modules.Molonies.Settings') => FiscalZone::SHIPPING,
            $this->trans('Company', 'Modules.Molonies.Settings') => FiscalZone::COMPANY,
        ];

        $this->addresses = [
            $this->trans('Moloni company', 'Modules.Molonies.Settings') => LoadAddress::MOLONI,
            $this->trans('Custom address', 'Modules.Molonies.Settings') => LoadAddress::CUSTOM,
        ];

        $this->customerLanguage = [
            $this->trans('Automatic', 'Modules.Molonies.Settings') => 0,
            $this->trans('Language', 'Modules.Molonies.Settings') => [
                $this->trans('Portuguese', 'Modules.Molonies.Settings') => Languages::PT,
                $this->trans('Spanish', 'Modules.Molonies.Settings') => Languages::ES,
                $this->trans('English', 'Modules.Molonies.Settings') => Languages::EN,
            ],
        ];

        if (!empty($this->stores)) {
            $this->addresses[$this->trans('Stores', 'Modules.Molonies.Settings')] = $this->stores;
        }

        return $this;
    }

    /**
     * Simple translator implementation
     *
     * @param string $string
     * @param string $domain
     *
     * @return string
     *
     * @noinspection PhpSameParameterValueInspection
     */
    private function trans(string $string, string $domain): string
    {
        return $this->translator->trans($string, [], $domain);
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

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @return array
     */
    public function getSyncFields(): array
    {
        return $this->syncFields;
    }

    /**
     * @return array
     */
    public function getDocumentReference(): array
    {
        return $this->documentReference;
    }

    /**
     * @return array
     */
    public function getCustomerLanguage(): array
    {
        return $this->customerLanguage;
    }

    /**
     * @return array
     */
    public function getExemptionReasons(): array
    {
        return $this->exemptionReasons;
    }
}
