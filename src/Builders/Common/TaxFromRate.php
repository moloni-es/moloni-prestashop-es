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

namespace Moloni\Builders\Common;

use Moloni\Api\MoloniApiClient;
use Moloni\Builders\Interfaces\BuilderItemInterface;
use Moloni\Enums\Boolean;
use Moloni\Enums\Countries;
use Moloni\Enums\TaxFiscalZoneType;
use Moloni\Enums\TaxType;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Exceptions\MoloniException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class TaxFromRate implements BuilderItemInterface
{
    /**
     * Moloni tax id
     *
     * @var int
     */
    protected $taxId = 0;

    /**
     * Tax type
     *
     * @var int
     */
    protected $type;

    /**
     * Fiscal zone finance type
     *
     * @var int
     */
    protected $fiscalZoneType;

    /**
     * Tax name
     *
     * @var string
     */
    protected $name;

    /**
     * Tax rate value
     *
     * @var float
     */
    protected $value;

    /**
     * If tax is cumulative
     *
     * @var bool
     */
    protected $cumulative;

    /**
     * Fiscal zone
     *
     * @var array
     */
    protected $fiscalZone;

    /**
     * Tax order
     *
     * @var int
     */
    protected $taxOrder;

    /**
     * Constructor
     *
     * @param float $taxRate Order product tax line
     * @param array|null  $fiscalZone Document fiscal zone
     * @param int|null $taxOrder Tax order
     */
    public function __construct(float $taxRate, ?array $fiscalZone, ?int $taxOrder = 1)
    {
        if (empty($fiscalZone)) {
            $fiscalZone = [
                'code' => 'ES',
                'countryId' => Countries::SPAIN
            ];
        }

        $this->value = $taxRate;
        $this->fiscalZone = $fiscalZone;
        $this->taxOrder = $taxOrder;

        $this->init();
    }

    //          PUBLICS          //

    /**
     * Creates a tax in Moloni
     *
     * @return TaxFromRate
     *
     * @throws MoloniException
     */
    public function insert(): TaxFromRate
    {
        try {
            $params = [
                'data' => [
                    'visible' => Boolean::YES,
                    'name' => $this->name,
                    'fiscalZone' => $this->fiscalZone['code'],
                    'fiscalZoneFinanceType' => $this->fiscalZoneType,
                    'countryId' => $this->fiscalZone['countryId'],
                    'type' => $this->type,
                    'isDefault' => false,
                    'value' => $this->value,
                ]
            ];

            $fiscalZoneSettings = $this->getFiscalZoneTaxSettings();

            if (!empty($fiscalZoneSettings) && in_array($fiscalZoneSettings['visible'], ['TYPESELECTED', 'ALWAYS']) && $fiscalZoneSettings['type'] === 'VALUES') {
                $params['data']['fiscalZoneFinanceTypeMode'] = 'NOR';
            }

            $mutation = MoloniApiClient::taxes()->mutationTaxCreate($params);

            $taxId = $mutation['data']['taxCreate']['data']['taxId'] ?? 0;

            if ((int) $taxId > 0) {
                $this->taxId = (int) $taxId;
            } else {
                throw new MoloniException('Error creating tax: ({0} - {1})', [
                    '{0}' => $this->name,
                    '{1}' => $this->value,
                ], ['params' => $params, 'response' => $mutation]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error creating tax: ({0} - {1})', [
                '{0}' => $this->name,
                '{1}' => $this->value,
            ], $e->getData());
        }

        return $this;
    }

    /**
     * Searches tax in Moloni
     *
     * @return TaxFromRate
     *
     * @throws MoloniException
     */
    public function search(): TaxFromRate
    {
        $this->getByValueAndFiscalZone();

        return $this;
    }

    /**
     * Exports tax data to array format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'taxId' => $this->taxId,
            'value' => $this->value,
            'ordering' => $this->taxOrder,
            'cumulative' => $this->cumulative,
        ];
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @return $this
     */
    protected function init(): TaxFromRate
    {
        $this
            ->setType()
            ->setFiscalZoneType()
            ->setComulative()
            ->setName();

        return $this;
    }

    //          GETS          //

    /**
     * Tax id getter
     *
     * @return int
     */
    public function getTaxId(): int
    {
        return $this->taxId;
    }

    //          SETS          //

    /**
     * Defines tax type
     *
     * @return $this
     */
    public function setType(): TaxFromRate
    {
        $this->type = TaxType::PERCENTAGE;

        return $this;
    }

    /**
     * Defines tax type
     *
     * @return $this
     */
    public function setFiscalZoneType(): TaxFromRate
    {
        $this->fiscalZoneType = TaxFiscalZoneType::VAT;

        return $this;
    }

    /**
     * Defines if tax is comulative
     *
     * @return $this
     */
    public function setComulative(): TaxFromRate
    {
        $this->cumulative = false;

        return $this;
    }

    /**
     * Define tax name
     *
     * @return $this
     */
    public function setName(): TaxFromRate
    {
        $this->name = 'VAT - ' . $this->fiscalZone['code'] . ' - ' . $this->value;

        return $this;
    }

    //          REQUESTS          //

    /**
     * Search for fiscal zone tax settings
     *
     * @return array
     *
     * @throws MoloniException
     */
    public function getFiscalZoneTaxSettings(): array
    {
        $variables = [
            'fiscalZone' => $this->fiscalZone['code'] ?? ''
        ];

        try {
            $query = MoloniApiClient::fiscalZone()->queryFiscalZoneTaxSettings($variables);

            return $query['data']['fiscalZoneTaxSettings']['fiscalZoneModes'][0] ?? [];
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error fetching fiscal zone tax settings', [], $e->getData());
        }
    }

    /**
     * Search for taxes by value and fiscal zone
     *
     * @throws MoloniException
     */
    public function getByValueAndFiscalZone(): void
    {
        $variables = [
            'options' => [
                'filter' => [
                    [
                        'field' => 'value',
                        'comparison' => 'eq',
                        'value' => (string)$this->value
                    ],
                    [
                        'field' => 'flags',
                        'comparison' => 'eq',
                        'value' => '0'
                    ],
                    [
                        'field' => 'type',
                        'comparison' => 'eq',
                        'value' => (string)$this->type
                    ],
                    [
                        'field' => 'fiscalZoneFinanceType',
                        'comparison' => 'eq',
                        'value' => (string)$this->fiscalZoneType
                    ]
                ],
                'search' => [
                    'field' => 'fiscalZone',
                    'value' => $this->fiscalZone['code']
                ]
            ],
        ];

        try {
            $query = MoloniApiClient::taxes()
                ->queryTaxes($variables);

            if (!empty($query) && isset($query[0]['taxId'])) {
                $this->taxId = $query[0]['taxId'];
            }
        } catch (MoloniApiException $e) {
            throw new MoloniException('Error fetching taxes', [], $e->getData());
        }
    }
}
