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

namespace Moloni\Api;

use Moloni\Api\Endpoints\Categories;
use Moloni\Api\Endpoints\Companies;
use Moloni\Api\Endpoints\Countries;
use Moloni\Api\Endpoints\Currencies;
use Moloni\Api\Endpoints\Customers;
use Moloni\Api\Endpoints\DeliveryMethods;
use Moloni\Api\Endpoints\Documents;
use Moloni\Api\Endpoints\DocumentSets;
use Moloni\Api\Endpoints\FiscalZone;
use Moloni\Api\Endpoints\GeographicZones;
use Moloni\Api\Endpoints\Hooks;
use Moloni\Api\Endpoints\Languages;
use Moloni\Api\Endpoints\MaturityDates;
use Moloni\Api\Endpoints\MeasurementUnits;
use Moloni\Api\Endpoints\PaymentMethods;
use Moloni\Api\Endpoints\PriceClasses;
use Moloni\Api\Endpoints\Products;
use Moloni\Api\Endpoints\PropertyGroups;
use Moloni\Api\Endpoints\Stock;
use Moloni\Api\Endpoints\Taxes;
use Moloni\Api\Endpoints\Timezones;
use Moloni\Api\Endpoints\Warehouses;

class MoloniApiClient
{
    /**
     * @var Categories|null
     */
    private static $categories;
    /**
     * @var Warehouses|null
     */
    private static $warehouses;
    /**
     * @var Companies|null
     */
    private static $companies;
    /**
     * @var Countries|null
     */
    private static $countries;
    /**
     * @var Currencies|null
     */
    private static $currencies;
    /**
     * @var Customers|null
     */
    private static $customers;
    /**
     * @var DeliveryMethods|null
     */
    private static $deliveryMethods;
    /**
     * @var Documents|null
     */
    private static $documents;
    /**
     * @var DocumentSets|null
     */
    private static $documentSets;
    /**
     * @var FiscalZone|null
     */
    private static $fiscalZone;
    /**
     * @var GeographicZones|null
     */
    private static $geographicZones;
    /**
     * @var Hooks|null
     */
    private static $hooks;
    /**
     * @var Languages|null
     */
    private static $languages;
    /**
     * @var MaturityDates|null
     */
    private static $maturityDates;
    /**
     * @var MeasurementUnits|null
     */
    private static $measurementUnits;
    /**
     * @var PaymentMethods|null
     */
    private static $paymentMethods;
    /**
     * @var PriceClasses|null
     */
    private static $priceClasses;
    /**
     * @var Stock|null
     */
    private static $stock;
    /**
     * @var Timezones|null
     */
    private static $timezones;
    /**
     * @var Taxes|null
     */
    private static $taxes;
    /**
     * @var Products|null
     */
    private static $products;
    /**
     * @var PropertyGroups|null
     */
    private static $propertyGroups;

    public static function categories(): Categories
    {
        if (!self::$categories) {
            self::$categories = new Categories();
        }

        return self::$categories;
    }

    public static function companies(): Companies
    {
        if (!self::$companies) {
            self::$companies = new Companies();
        }

        return self::$companies;
    }

    public static function countries(): Countries
    {
        if (!self::$countries) {
            self::$countries = new Countries();
        }

        return self::$countries;
    }

    public static function currencies(): Currencies
    {
        if (!self::$currencies) {
            self::$currencies = new Currencies();
        }

        return self::$currencies;
    }

    public static function customers(): Customers
    {
        if (!self::$customers) {
            self::$customers = new Customers();
        }

        return self::$customers;
    }

    public static function deliveryMethods(): DeliveryMethods
    {
        if (!self::$deliveryMethods) {
            self::$deliveryMethods = new DeliveryMethods();
        }

        return self::$deliveryMethods;
    }

    public static function documents(): Documents
    {
        if (!self::$documents) {
            self::$documents = new Documents();
        }

        return self::$documents;
    }

    public static function documentSets(): DocumentSets
    {
        if (!self::$documentSets) {
            self::$documentSets = new DocumentSets();
        }

        return self::$documentSets;
    }

    public static function fiscalZone(): FiscalZone
    {
        if (!self::$fiscalZone) {
            self::$fiscalZone = new FiscalZone();
        }

        return self::$fiscalZone;
    }

    public static function geographicZones(): GeographicZones
    {
        if (!self::$geographicZones) {
            self::$geographicZones = new GeographicZones();
        }

        return self::$geographicZones;
    }

    public static function hooks(): Hooks
    {
        if (!self::$hooks) {
            self::$hooks = new Hooks();
        }

        return self::$hooks;
    }

    public static function languages(): Languages
    {
        if (!self::$languages) {
            self::$languages = new Languages();
        }

        return self::$languages;
    }

    public static function maturityDates(): MaturityDates
    {
        if (!self::$maturityDates) {
            self::$maturityDates = new MaturityDates();
        }

        return self::$maturityDates;
    }

    public static function measurementUnits(): MeasurementUnits
    {
        if (!self::$measurementUnits) {
            self::$measurementUnits = new MeasurementUnits();
        }

        return self::$measurementUnits;
    }

    public static function paymentMethods(): PaymentMethods
    {
        if (!self::$paymentMethods) {
            self::$paymentMethods = new PaymentMethods();
        }

        return self::$paymentMethods;
    }

    public static function priceClasses(): PriceClasses
    {
        if (!self::$priceClasses) {
            self::$priceClasses = new PriceClasses();
        }

        return self::$priceClasses;
    }

    public static function products(): Products
    {
        if (!self::$products) {
            self::$products = new Products();
        }

        return self::$products;
    }

    public static function propertyGroups(): PropertyGroups
    {
        if (!self::$propertyGroups) {
            self::$propertyGroups = new PropertyGroups();
        }

        return self::$propertyGroups;
    }

    public static function stock(): Stock
    {
        if (!self::$stock) {
            self::$stock = new Stock();
        }

        return self::$stock;
    }

    public static function taxes(): Taxes
    {
        if (!self::$taxes) {
            self::$taxes = new Taxes();
        }

        return self::$taxes;
    }

    public static function timezones(): Timezones
    {
        if (self::$timezones) {
            self::$timezones = new Timezones();
        }

        return self::$timezones;
    }

    public static function warehouses(): Warehouses
    {
        if (!self::$warehouses) {
            self::$warehouses = new Warehouses();
        }

        return self::$warehouses;
    }
}
