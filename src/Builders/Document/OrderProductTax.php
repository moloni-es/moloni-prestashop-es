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

namespace Moloni\Builders\Document;

use Tax;
use Moloni\Enums\Boolean;
use Moloni\Api\MoloniApiClient;
use Moloni\Enums\SaftType;
use Moloni\Exceptions\Document\MoloniDocumentProductTaxException;
use Moloni\Exceptions\MoloniApiException;
use Moloni\Builders\Interfaces\BuilderItemInterface;

class OrderProductTax implements BuilderItemInterface
{
    /**
     * Moloni tax id
     *
     * @var int
     */
    public $taxId = 0;

    /**
     * Tax saft type
     *
     * @var int
     */
    protected $type;

    /**
     * Product tax data
     *
     * @var Tax
     */
    protected $productTax;

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
     * Fiscal zone
     *
     * @var string
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
     * @param Tax $productTax Order product tax line
     * @param string $fiscalZone Document fiscal zone
     * @param int $taxOrder Tax order
     */
    public function __construct(Tax $productTax, string $fiscalZone, int $taxOrder)
    {
        $this->productTax = $productTax;
        $this->fiscalZone = $fiscalZone;
        $this->taxOrder = $taxOrder;

        $this->init();
    }

    //          PUBLICS          //

    /**
     * Creates a tax in Moloni
     *
     * @return OrderProductTax
     *
     * @throws MoloniDocumentProductTaxException
     */
    public function insert(): OrderProductTax
    {
        try {
            $params = [
                'data' => [
                    'visible' => Boolean::YES,
                    'name' => $this->name,
                    'fiscalZone' => $this->fiscalZone,
                    'type' => $this->type,
                    'isDefault' => false,
                    'value' => $this->value,
                ]
            ];

            $mutation = MoloniApiClient::taxes()->mutationTaxCreate($params);

            $taxId = $mutation['data']['taxCreate']['data']['taxId'] ?? 0;

            if ((int) $taxId > 0) {
                $this->taxId = (int) $taxId;
            } else {
                throw new MoloniDocumentProductTaxException('Error creating product tax: ({0} - {1})', [
                    '{0}' => $this->name,
                    '{1}' => $this->value,
                ], ['params' => $params, 'response' => $mutation]);
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentProductTaxException('Error creating product tax: ({0} - {1})', [
                '{0}' => $this->name,
                '{1}' => $this->value,
            ], $e->getData());
        }

        return $this;
    }

    /**
     * Searches tax in Moloni
     *
     * @throws MoloniDocumentProductTaxException
     */
    public function search(): OrderProductTax
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
            'ordering' => $this->taxOrder,
            'value' => $this->value
        ];
    }

    //          PRIVATES          //

    /**
     * Start initial values
     *
     * @return $this
     */
    protected function init(): OrderProductTax
    {
        $this
            ->setType()
            ->setValue()
            ->setName();

        return $this;
    }

    //          SETS          //

    /**
     * Defines tax rate value
     *
     * @return $this
     */
    public function setValue(): OrderProductTax
    {
        $this->value = $this->productTax->rate;

        return $this;
    }

    /**
     * Defines tax type
     *
     * @return $this
     */
    public function setType(): OrderProductTax
    {
        $this->type = SaftType::IVA;

        return $this;
    }

    /**
     * Define tax name
     *
     * @return $this
     */
    public function setName(): OrderProductTax
    {
        $this->name = 'VAT - ' . $this->fiscalZone . ' - ' . $this->value;

        return $this;
    }

    //          REQUESTS          //

    /**
     * Search for taxes by value and fiscal zone
     *
     * @throws MoloniDocumentProductTaxException
     */
    public function getByValueAndFiscalZone(): void
    {
        $variables = [
            'options' => [
                'filter' => [
                    'field' => 'value',
                    'comparison' => 'eq',
                    'value' => (string)$this->value
                ],
                'search' => [
                    'field' => 'fiscalZone',
                    'value' => $this->fiscalZone
                ]
            ],
        ];

        try {
            $query = MoloniApiClient::taxes()
                ->queryTaxes($variables);

            foreach ($query as $tax) {
                if (empty($tax['flags'])) {
                    $this->taxId = $tax['taxId'];

                    break;
                }
            }
        } catch (MoloniApiException $e) {
            throw new MoloniDocumentProductTaxException('Error fetching taxes', [], $e->getData());
        }
    }
}
