<?php

namespace Moloni\ES\Controllers\Models;

use Moloni\ES\Controllers\Api\Categories;
use Moloni\ES\Controllers\Api\Companies;
use Moloni\ES\Controllers\Api\MeasurementUnits;
use Moloni\ES\Controllers\Api\Products;
use Moloni\ES\Controllers\Api\Stock;
use Moloni\ES\Controllers\Api\Taxes;
use PrestaShop\PrestaShop\Adapter\Entity\Product as psProduct;
use PrestaShopBundle\Translation\TranslatorComponent;
use PrestaShopDatabaseException;

class Product
{
    /**
     * @var psProduct Product created in prestashop
     */
    private $productPs;

    public $productId;
    public $productCategoryId;
    public $productCategoryName;
    public $reference;
    public $name;
    public $price;
    public $priceWithTax;
    public $stock;
    public $measurementUnitId;
    public $measurementUnitName;
    public $type;
    public $summary;
    public $taxId;
    public $taxName;
    public $taxValue;
    public $warehouseId;
    public $discount; //documents variable
    public $ordering; //documents variable
    public $qty; //documents variable

    //Need to be defined like this, in my understanding
    private $fiscalZoneFinanceType = 1;
    private $fiscalZoneFinanceTypeMode = 'NOR';
    private $taxType = 1;
    private $hasStock = true;
    private $exemptionReason = '';

    /**
     * translator component
     */
    public $translator;

    /**
     * Product constructor.
     *
     * @param TranslatorComponent $translator
     */
    public function __construct(psProduct $productPs, $translator)
    {
        $this->productPs = $productPs;
        $this->translator = $translator;
    }

    /**
     * Populates the vars with the prestashop product object values
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function init()
    {
        $this->reference = $this->productPs->reference;
        $this->name = $this->productPs->name;
        $this->price = $this->productPs->price;

        if (!isset($this->priceWithTax)) {
            $this->priceWithTax = $this->productPs->getPrice();
        }

        $this->taxName = $this->productPs->tax_name;
        $this->taxValue = (float)$this->productPs->tax_rate;

        $this->productCategoryName = (new \Category(
            $this->productPs->id_category_default,
            \Configuration::get('PS_LANG_DEFAULT')
        )
        )->name;

        $this->summary = strip_tags($this->productPs->description_short);
        $this->measurementUnitName = $this->productPs->unity;

        //looks if the product exists in moloni by searching by its reference
        $this->loadByReference();

        //reference cannot be empty in moloni
        if (empty($this->reference)) {
            $this->addError($this->translator->trans(
                'Cannot sync product, reference is null!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        if (!$this->setCategory() ||
            !$this->setType() ||
            !$this->setMeasurementUnits() ||
            !$this->setTaxes()) {
            return false;
        }

        return true;
    }

    /**
     * Loads an product form moloni based on reference
     *
     * @return bool true or false
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopDatabaseException
     */
    public function loadByReference()
    {
        $variables = ['companyId' => (int) Company::get('company_id'),
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
                'Error fetching products!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        //if found, set some data
        if (!empty($queryResult)) {
            foreach ($queryResult as $query) {
                if ((int) $query['reference'] == (int) $this->reference) {
                    $this->productId = $query['productId'];
                    if ($query['hasStock'] == true) {
                        $this->stock = $query['stock'];
                        $this->warehouseId = (int) $query['warehouse']['warehouseId'];
                    } else {
                        $this->stock = $this->productPs->quantity;
                        $this->warehouseId = Settings::get('Warehouse');
                    }
                }
            }
        } else {
            $this->stock = $this->productPs->quantity;
            $this->warehouseId = Settings::get('Warehouse');
        }

        return true;
    }

    /**
     * Updates or creates a product on moloni
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function create()
    {
        //this value is not null if loadByReference() method found the product on moloni
        if (!empty($this->productId)) {
            $mutation = Products::mutationProductUpdate($this->setVariables());

            if (isset($mutation['errors'])) {
                $this->addError($this->translator->trans(
                    'Something went wrong updating the product!!',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
            }

            $mutation = $mutation['data']['productUpdate']['data'];
            $this->productId = $mutation['productId'];

            Log::writeLog($this->translator->trans(
                'Updated product: %name% | Reference: %reference%',
                ['%name%' => $this->name, '%reference%' => $this->reference],
                'Modules.Moloniprestashopes.Success'
            ));

            //if stock sync is enabled in settings, syncs stock
            if ((Settings::get('Stocks') == 1)) {
                $this->setStock();
            }
        } else {
            $mutation = Products::mutationProductCreate($this->setVariables());

            if (isset($mutation['errors'])) {
                $this->addError($this->translator->trans(
                    'Something went wrong creating the product!!',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
            }

            $mutation = $mutation['data']['productCreate']['data'];
            $this->productId = $mutation['productId'];

            Log::writeLog($this->translator->trans(
                'Created product: %name% | Reference: %reference%',
                ['%name%' => $this->name, '%reference%' => $this->reference],
                'Modules.Moloniprestashopes.Success'
            ));
        }

        return true;
    }

    /**
     * Sets the type of the product
     *
     * @return bool
     */
    public function setType()
    {
        //1-Product 2-Service 3-Others
        $this->type = 1;

        return true;
    }

    /**
     * Checks if the category exists on moloni account, if not creates it
     *
     * @return bool
     */
    public function setCategory()
    {
        $categoriesArray = self::getCategoryTree($this->productPs->id_category_default);

        $this->productCategoryId = 0;
        foreach ($categoriesArray as $category) {
            $categoryObj = new ProductCategory($category, $this->productCategoryId);

            if (!$categoryObj->loadByName()) {
                $categoryObj->create();
            }

            $this->productCategoryId = $categoryObj->categoryId;
        }

        if ($this->productCategoryId === 0) {
            $categoryName = 'Tienda online'; //todo: use translations
            $categoryObj = new ProductCategory($categoryName, 0);

            if (!$categoryObj->loadByName()) {
                $categoryObj->create();
            }

            $this->productCategoryId = $categoryObj->categoryId;
        }

        return true;
    }

    /**
     *Checks if the measurement units exists on moloni account, if not creates it
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function setMeasurementUnits()
    {
        if (empty(Settings::get('Measure'))) {
            $this->addError($this->translator->trans(
                'Please select a measurement unit in settings!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        //if a default measurement unit is chosen in settings, use its id
        if (Settings::get('Measure') != 'LetPresta') {
            $this->measurementUnitId = (int) Settings::get('Measure');

            return true;
        }

        //if the let presta chosen
        if (empty($this->measurementUnitName)) {
            $this->addError($this->translator->trans(
                'Cannot sync product, measurement unit name is null',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $variables = ['companyId' => (int) Company::get('company_id'),
            'options' => [
                'search' => [
                    'field' => 'ALL',
                    'value' => $this->measurementUnitName,
                ],
            ],
        ];

        $queryResult = (MeasurementUnits::queryMeasurementUnits($variables));

        if ($queryResult === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching the measurement units!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        //if it already exists, get its id. Otherwise create new measurement unit
        if (!empty($queryResult)) {
            $this->measurementUnitId = $queryResult[0]['measurementUnitId'];
        } else {
            $variables = ['companyId' => (int) Company::get('company_id'),
                'data' => [
                    'name' => $this->measurementUnitName,
                    'abbreviation' => $this->measurementUnitName,
                ],
            ];

            $aux = MeasurementUnits::mutationMeasurementUnitCreate($variables);

            if (isset($aux['errors'])) {
                $this->addError($this->translator->trans(
                    'Something went wrong, cannot create measurement unit!!',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
            }

            Log::writeLog($this->translator->trans(
                'Created category ( %measurementUnitName% ) for %name% .',
                ['%measurementUnitName%' => $this->measurementUnitName, '%name%' => $this->name],
                'Modules.Moloniprestashopes.Success'
            ));

            $this->measurementUnitId = (int) ($aux)['data']['measurementUnitCreate']['data']['measurementUnitId'];
        }

        return true;
    }

    /**
     * Checks if the taxes exists on moloni account, if not creates it
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function setTaxes()
    {
        if (empty(Settings::get('Tax'))) {
            $this->addError($this->translator->trans(
                'Please select a tax in settings!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        //if an default tax is set in settings, use its id.
        if (Settings::get('Tax') !== 'LetPresta' && is_numeric(Settings::get('Tax'))) {
            $this->taxId = (int) Settings::get('Tax');

            $variables = ['companyId' => (int) Company::get('company_id'),
                'taxId' => $this->taxId,
            ];

            $query = Taxes::queryTax($variables);

            if (isset($query['errors'])) {
                $this->addError($this->translator->trans(
                    'Something went wrong getting a tax!!',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
            }

            $query = $query['data']['tax']['data']['value'];
            $this->taxValue = (float)$query;

            //if the tax is set, calculate the product price
            $this->price = ($this->priceWithTax * 100);
            $this->price /= (100 + $this->taxValue);

            return true;
        }

        //in case tax value is 0 or in settings is the default selected option is "isento"
        if ($this->taxValue === 0 || Settings::get('Tax') === 'isento') {
            //set value to 0 because if tax is "isento", in setVariables whe need to send taxes empty
            $this->taxValue = 0;
            //"isento" reasons
            $this->price = $this->priceWithTax;
            $this->exemptionReason = Settings::get('Exemption');
            if (empty($this->exemptionReason)) {
                $this->addError($this->translator->trans(
                    'Please select an exemption reason in settings!!',
                    [],
                    'Modules.Moloniprestashopes.Errors'
                ));

                return false;
            }

            return true;
        }

        $variables = ['companyId' => (int) Company::get('company_id')];
        $queryResult = (Taxes::queryTaxes($variables));

        if ($queryResult === false) {
            $this->addError($this->translator->trans(
                'Something went wrong fetching taxes!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        //Sets the tax percentage to the first entry tax with the same value
        foreach ($queryResult as $tax) {
            if (round((float)$tax['value'], 2) === round($this->taxValue, 2)) {
                $this->taxId = $tax['taxId'];
                $this->taxValue = $tax['value'];

                return true;
            }
        }

        //if no equal value tax found, create a new tax in moloni
        $variables = ['companyId' => (int) Company::get('company_id')];

        //fetch company and financial info
        $queryResult = (Companies::queryCompany2($variables))['data']['company']['data'];

        if (isset($queryResult['errors'])) {
            $this->addError($this->translator->trans(
                'Something went fetching company info!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $variables = ['companyId' => (int) Company::get('company_id'),
            'data' => [
                'name' => $this->taxName,
                'fiscalZone' => $queryResult['fiscalZone']['fiscalZone'],
                'countryId' => $queryResult['country']['countryId'],
                'type' => $this->taxType,
                'fiscalZoneFinanceType' => $this->fiscalZoneFinanceType,
                'value' => $this->taxValue,
                'fiscalZoneFinanceTypeMode' => $this->fiscalZoneFinanceTypeMode,
            ],
        ];

        $queryResult = Taxes::mutationTaxCreate($variables);

        if (isset($queryResult['errors'])) {
            $this->addError($this->translator->trans(
                'Something went creating tax!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        $queryResult = $queryResult['data']['taxCreate']['data'];
        $this->taxId = $queryResult['taxId'];
        $this->taxValue = $queryResult['value'];

        Log::writeLog($this->translator->trans(
            'Created tax ( %taxName% ) for %name% .',
            ['%taxName%' => $this->taxName, '%name%' => $this->name],
            'Modules.Moloniprestashopes.Success'
        ));

        return true;
    }

    /**
     * Checks of the product stock is updated, if not it gets updated
     *
     * @return bool
     */
    private function setStock()
    {
        //if the stock is up-to-date do nothing
        if ($this->stock === $this->productPs->quantity) {
            Log::writeLog($this->translator->trans(
                'Stock is up-to-date!!',
                [],
                'Modules.Moloniprestashopes.Success'
            ));

            return true;
        }

        $variables = ['companyId' => (int) Company::get('company_id'),
            'data' => [
                'productId' => $this->productId,
                'notes' => 'Prestashop',
                'warehouseId' => (int) $this->warehouseId,
            ],
        ];

        //if prestashop stock is higher, create an manual exit movement on moloni with the deference
        //else create an manual entry movement
        if ((int) $this->stock > (int) $this->productPs->quantity) {
            $variables['data']['qty'] = (float) ($this->stock - $this->productPs->quantity);
            $queryResult = Stock::mutationStockMovementManualExitCreate($variables);
        } else {
            $variables['data']['qty'] = (float) ($this->productPs->quantity - $this->stock);
            $queryResult = Stock::mutationStockMovementManualEntryCreate($variables);
        }

        if (isset($queryResult['errors'])) {
            $this->addError($this->translator->trans(
                'Error updating stock!!',
                [],
                'Modules.Moloniprestashopes.Errors'
            ));

            return false;
        }

        Log::writeLog($this->translator->trans(
            'Stock updated (old: %oStock% | new: %nStock% ) for %name% .',
            ['%oStock%' => (int) $this->stock, '%nStock%' => (int) $this->productPs->quantity, '%name%' => $this->name],
            'Modules.Moloniprestashopes.Success'
        ));

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
            'companyId' => (int) Company::get('company_id'),
            'data' => [
                'productCategoryId' => $this->productCategoryId,
                'type' => $this->type,
                'reference' => $this->reference,
                'name' => $this->name,
                'measurementUnitId' => $this->measurementUnitId,
                'price' => $this->price,
                'summary' => $this->summary,
                'exemptionReason' => $this->exemptionReason,
                'hasStock' => $this->hasStock,
                'taxes' => [
                    [
                        'taxId' => $this->taxId,
                        'value' => $this->taxValue,
                        'ordering' => 1,
                        'cumulative' => false,
                    ],
                ],
            ],
        ];

        //if its to update, set the id in the variables
        if (!empty($this->productId)) {
            $variables['data']['productId'] = $this->productId;
        } else {
            //its an create, so set the stock in the chosen settings warehouse
            if (!empty($this->warehouseId)) {
                $variables['data']['warehouseId'] = (int) $this->warehouseId;
                $variables['data']['warehouses'] = [
                    [
                        'warehouseId' => (int) $this->warehouseId,
                        'stock' => (float) $this->stock,
                    ],
                ];
            }
        }

        //if the tax is exempt, remove the taxes value to empty
        if ($this->taxValue == 0) {
            $variables['data']['taxes'] = [];
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
            'Product: %name% Reference: %reference% Message: %msg%',
            ['%name%' => $this->productPs->name, '%reference%' => $this->productPs->reference, '%msg%' => $msg],
            'Modules.Moloniprestashopes.Errors'
        ));

        return true;
    }

    /**
     * Sets the variables for the products when creating a document
     *
     * @param $cartProduct array Product in cart with price information
     *
     * @return array|bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function setVariablesForDocument($cartProduct)
    {
        if (!isset($this->productId)) {
            return false;
        }

        $this->price = $cartProduct['unit_price_tax_excl'];
        $this->priceWithTax = $cartProduct['unit_price_tax_incl'];

        if ($cartProduct['unit_price_tax_incl'] !== $cartProduct['unit_price_tax_excl']) {
            $this->taxValue =
                (100 * ($cartProduct['unit_price_tax_incl']
                        - $cartProduct['unit_price_tax_excl'])) / $cartProduct['unit_price_tax_excl'];
        } else {
            $this->taxValue = 0;
        }

        if (!$this->setTaxes()) {
            return false;
        }

        $variables = [
            'productId' => $this->productId,
            'price' => (float) $this->price,
            'summary' => $this->summary,
            'exemptionReason' => $this->exemptionReason,
            'ordering' => $this->ordering,
            'qty' => (float) $this->qty,
            'discount' => $this->discount,
            'taxes' => [
                [
                    'taxId' => $this->taxId,
                    'value' => $this->taxValue,
                    'ordering' => 1,
                    'cumulative' => false,
                ],
            ],
        ];

        //if the tax is exempt, remove the taxes value to empty
        if ($this->taxValue === 0) {
            $variables['taxes'] = [];
        }

        return $variables;
    }

    /**
     * Returns all prestashop categories above the category received
     *
     * @param $categoryId
     *
     * @return array
     */
    public static function getCategoryTree($categoryId)
    {
        $lang = (int) \Configuration::get('PS_LANG_DEFAULT');

        $categories = [];
        $failsafe = 0;
        $currentId = $categoryId;

        do {
            $category = new \Category($currentId, $lang);
            $currentId = $category->id_parent;
            array_unshift($categories, $category->name); //order needs to be inverted

            ++$failsafe;
        } while (!in_array((int) $currentId, [1, 2]) && $failsafe < 100);

        return $categories;
    }
}
