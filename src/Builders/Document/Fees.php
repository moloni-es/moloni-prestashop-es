<?php

namespace Moloni\Builders\Document;

use Moloni\Api\Endpoints\Categories;
use Moloni\Api\Endpoints\Companies;
use Moloni\Api\Endpoints\Products;
use Moloni\Api\Endpoints\Taxes;
use Moloni\Helpers\Error;
use Moloni\Helpers\Log;
use Moloni\Helpers\Moloni;
use Moloni\Helpers\Settings;
use Order;
use PrestaShopBundle\Translation\TranslatorComponent;
use PrestaShopDatabaseException;

class Fees
{
    public $productId;
    public $categoryId;
    public $measurementUnitId;
    public $taxId;
    public $taxValue;
    public $price;
    public $priceWithTax;
    public $summary = 'Cargos de envío';
    public $exemptionReason;
    public $name;
    public $categoryName;
    public $reference;
    public $type;
    public $qty = 1;
    public $hasStock = 0;
    public $stock = 0;
    public $taxCumulative = false;
    public $ordering;
    public $discount = 0;

    // prestashop order
    public $psOrder;

    /**
     * translator component
     */
    public $translator;

    /**
     * Fees constructor.
     *
     * @param Order $psOrder Order
     * @param TranslatorComponent $translator translator component
     */
    public function __construct(Order $psOrder, $translator)
    {
        $this->psOrder = $psOrder;
        $this->translator = $translator;
    }

    /**
     * Instantiates the class
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function init()
    {
        $this->price = ($this->psOrder->getShipping())[0]['shipping_cost_tax_excl'];
        $this->priceWithTax = ($this->psOrder->getShipping())[0]['shipping_cost_tax_incl'];
        $this->taxValue = $this->psOrder->carrier_tax_rate;

        if (empty($this->productId)) {
            if (!$this->setReference() ||
                !$this->setType() ||
                !$this->setName() ||
                !$this->setDiscount() ||
                !$this->setMeasurementUnits()) {
                return false;
            }
        }

        // looks if the shipping product has already been created
        $this->loadByReference();

        // updates the taxes to this particular case
        if (!$this->setTaxes()) {
            return false;
        }

        // no need to know the category id if the category already exists
        if (empty($this->productId)) {
            if (!$this->setCategory()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates a product on moloni
     *
     * @return bool
     */
    public function create()
    {
        $mutation = (Products::mutationProductCreate($this->setVariables()))['data']['productCreate']['data'];

        if (empty($mutation)) {
            $this->addError($this->translator->trans(
                'Something went wrong creating the product!!',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        $this->productId = $mutation['productId'];

        Log::writeLog($this->translator->trans(
            'Created fee: %name% | Reference: %reference%',
            ['%name%' => $this->name, '%reference%' => $this->reference],
            'Modules.Molonies.Success'
        ));

        return true;
    }

    /**
     * Loads an product form moloni based on reference
     *
     * @return bool true or false
     */
    public function loadByReference()
    {
        $variables = ['companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'reference',
                    'value' => $this->reference,
                ],
            ],
        ];

        $queryResult = (Products::queryProducts($variables));

        if ($queryResult === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching products!!',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        // if found, set some data
        if (!empty($queryResult)) {
            $this->productId = $queryResult[0]['productId'];
        }

        return true;
    }

    /**
     * Sets product name
     *
     * @return bool
     */
    public function setName()
    {
        if (empty($this->name)) {
            $this->name = ($this->psOrder->getShipping())[0]['carrier_name'];
        }

        return true;
    }

    /**
     * Sets the product reference
     *
     * @return bool
     */
    public function setReference()
    {
        if (empty($this->reference)) {
            $this->reference = 'envio';
        }

        return true;
    }

    /**
     * sets the price of the fee product
     *
     * @return bool
     */
    public function setDiscount()
    {
        // no discounts if shipping was free
        if ($this->psOrder->total_shipping = 0) {
            $this->price = 0;

            return true;
        }

        // if shipping is free
        if (!empty($this->psOrder->getCartRules())) {
            $freeShipping = false;
            $shippingName = '';

            foreach ($this->psOrder->getCartRules() as $rule) {
                if ($rule['free_shipping'] == 1) {
                    $freeShipping = true;
                    $shippingName = $rule['name'];
                }
            }

            if ($freeShipping == true) {
                $this->discount = 100;
                $this->summary = $shippingName;
                $this->price = 0;

                return true;
            }
        }

        // calculate price without taxes
        if ($this->psOrder->carrier_tax_rate > 0) {
            $this->price = (($this->psOrder->getShipping())[0]['shipping_cost_tax_incl'] * 100);
            $this->price = $this->price / (100 + $this->psOrder->carrier_tax_rate);
        }

        return true;
    }

    /**
     * Sets the measurement units using the chosen settings value
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function setMeasurementUnits()
    {
        if (empty(Settings::get('Measure'))) {
            $this->addError($this->translator->trans(
                'Please select a measure unit in settings!!',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        $this->measurementUnitId = (int) Settings::get('Measure');

        return true;
    }

    /**
     * Checks if the category exists on moloni account, if not creates it
     *
     * @return bool
     */
    public function setCategory()
    {
        if (empty($this->categoryName)) {
            $this->categoryName = 'Envío';
        }

        $variables = ['companyId' => (int) Moloni::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'name',
                    'value' => $this->categoryName,
                ],
            ],
        ];

        $queryResult = (Categories::queryProductCategories($variables));

        if ($queryResult === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching product categories!!',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        // if the category exists, sets the id of the first returned category
        if (!empty($queryResult)) {
            $this->categoryId = (int) $queryResult[0]['productCategoryId'];
        } else {
            $variables = ['companyId' => (int) Moloni::get('company_id'),
                'data' => [
                    'name' => $this->categoryName,
                ],
            ];

            $mutation = Categories::mutationProductCategoryCreate($variables);

            if (isset($mutation['errors'])) {
                $this->addError($this->translator->trans(
                    'Something went wrong creating the category!!',
                    [],
                    'Modules.Molonies.Errors'
                ));

                return false;
            }

            Log::writeLog($this->translator->trans(
                'Created category ( %categoryName% ) for %name% .',
                ['%categoryName%' => $this->categoryName, '%name%' => $this->name],
                'Modules.Molonies.Success'
            ));

            $this->categoryId = (int) ($mutation)['data']['productCategoryCreate']['data']['productCategoryId'];
        }

        return true;
    }

    /**
     * Sets the product taxes
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function setTaxes()
    {
        if (empty(Settings::get('TaxShipping'))) {
            $this->addError($this->translator->trans(
                'Please select a tax shipping in settings!!',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        // if an default Tax shipping is set in settings, use its id.
        if (Settings::get('TaxShipping') != 'LetPresta' && is_numeric(Settings::get('TaxShipping'))) {
            $this->taxId = (int) Settings::get('TaxShipping');

            $variables = ['companyId' => (int) Moloni::get('company_id'),
                'taxId' => $this->taxId,
            ];

            $query = Taxes::queryTax($variables);

            if (isset($query['errors'])) {
                $this->addError($this->translator->trans(
                    'Something went wrong fetching tax!!',
                    [],
                    'Modules.Molonies.Errors'
                ));

                return false;
            }

            $this->taxValue = $query['data']['tax']['data']['value'];

            // if the tax is set, calculate the product price
            $this->price = ($this->priceWithTax * 100);
            $this->price = round($this->price / (100 + $this->taxValue), 3);

            return true;
        }

        // in case tax value is 0 or in settings is the default selected option is "isento"
        if ($this->taxValue == 0 || Settings::get('TaxShipping') == 'isento') {
            $this->exemptionReason = Settings::get('Shipping');
            $this->price = $this->priceWithTax;

            if (empty($this->exemptionReason)) {
                $this->addError($this->translator->trans(
                    'Please select an shipping exemption reason in settings!!',
                    [],
                    'Modules.Molonies.Errors'
                ));

                return false;
            }

            return true;
        }

        $variables = ['companyId' => (int) Moloni::get('company_id')];
        $queryResult = Taxes::queryTaxes($variables);

        if ($queryResult === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching taxes!!',
                [],
                'Modules.Molonies.Errors'
            ));

            return false;
        }

        // Sets the tax percentage to the first entry tax with the same value
        foreach ($queryResult as $tax) {
            if ($tax['value'] == $this->taxValue) {
                $this->taxId = $tax['taxId'];
                break;
            }
        }

        // if no equal value tax found, create a new tax in moloni
        if (empty($queryResult) || empty($this->taxId)) {
            $variables = ['companyId' => (int) Moloni::get('company_id')];

            // fetch company and financial info
            $queryResult = Companies::queryCompany($variables);

            if (isset($queryResult['errors'])) {
                $this->addError($this->translator->trans(
                    'Something went fetching company info!!',
                    [],
                    'Modules.Molonies.Errors'
                ));

                return false;
            }

            $queryResult = $queryResult['data']['company']['data'];

            $variables = ['companyId' => (int) Moloni::get('company_id'),
                'data' => [
                    'name' => $this->name,
                    'fiscalZone' => $queryResult['fiscalZone']['fiscalZone'],
                    'countryId' => $queryResult['country']['countryId'],
                    'type' => 1,
                    'fiscalZoneFinanceType' => 1,
                    'value' => $this->taxValue,
                    'fiscalZoneFinanceTypeMode' => 'NOR',
                ],
            ];

            $queryResult = Taxes::mutationTaxCreate($variables);

            if (isset($queryResult['errors'])) {
                $this->addError($this->translator->trans(
                    'Something went wrong creating tax!!',
                    [],
                    'Modules.Molonies.Errors'
                ));

                return false;
            }

            $queryResult = $queryResult['data']['taxCreate']['data'];
            $this->taxId = $queryResult['taxId'];
            $this->taxValue = $queryResult['value'];

            Log::writeLog($this->translator->trans(
                'Created tax ( %name% ) for %name% .',
                ['%name%' => $this->name],
                'Modules.Molonies.Success'
            ));
        }

        return true;
    }

    /**
     * Sets the product type
     *
     * @return bool
     */
    public function setType()
    {
        if (empty($this->type)) {
            // its a service (shipping)
            $this->type = 2;
        }

        return true;
    }

    /**
     * Creates an array with the arguments to connect to API
     *
     * @return array variables array
     */
    public function setVariables()
    {
        $variables = [
            'companyId' => (int) Moloni::get('company_id'),
            'data' => [
                'productCategoryId' => (int) $this->categoryId,
                'type' => $this->type,
                'reference' => $this->reference,
                'name' => $this->name,
                'measurementUnitId' => $this->measurementUnitId,
                'price' => (float) $this->price,
                'summary' => $this->summary,
                'hasStock' => (bool) $this->hasStock,
                'taxes' => [
                    [
                        'taxId' => (int) $this->taxId,
                        'value' => (float) $this->taxValue,
                        'ordering' => 1,
                        'cumulative' => false,
                    ],
                ],
            ],
        ];

        // if the tax is exempt, remove the taxes value to empty
        if ($this->taxValue == 0) {
            $variables['data']['taxes'] = [];
            $variables['data']['exemptionReason'] = $this->exemptionReason;
        } else {
            $variables['data']['exemptionReason'] = '';
        }

        return $variables;
    }

    /**
     * Creates an array with the fees data do add to the document products
     *
     * @return array
     */
    public function getVariablesForDocuments()
    {
        $variables = [
                'productId' => (int) $this->productId,
                'ordering' => (int) $this->ordering,
                'qty' => (float) $this->qty,
                'discount' => (float) $this->discount,
                'name' => $this->name,
                'price' => (float) $this->price,
                'summary' => $this->summary,
                'taxes' => [
                    [
                        'taxId' => (int) $this->taxId,
                        'value' => (float) $this->taxValue,
                        'ordering' => 1,
                        'cumulative' => false,
                    ],
                ],
        ];

        // if the tax is exempt, remove the taxes value to empty
        if ($this->taxValue == 0) {
            $variables['taxes'] = [];
            $variables['exemptionReason'] = $this->exemptionReason;
        } else {
            $variables['exemptionReason'] = '';
        }

        return $variables;
    }

    /**
     * Adds an error to the error class and logs
     *
     * @param $msg string Error string
     *
     * @return bool
     */
    public function addError($msg)
    {
        Log::writeLog($msg);
        Error::addError($this->translator->trans(
            'Shippment fee: %msg%',
            ['%msg%' => $msg],
            'Modules.Molonies.Errors'
        ));

        return true;
    }
}
