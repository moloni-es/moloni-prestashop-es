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
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Moloni\Webservice;

use Attribute;
use AttributeGroup;
use Category;
use Combination;
use Configuration;
use Moloni\Api\Endpoints\Categories as apiCategories;
use Moloni\Api\Endpoints\Products;
use Moloni\Controller\Admin\General;
use Moloni\Helpers\Log;
use Moloni\Helpers\LogSync;
use Moloni\Helpers\Settings;
use Product;
use StockAvailable;
use WebserviceOutputBuilder;
use WebserviceOutputBuilderCore;
use WebserviceRequest;
use WebserviceRequestCore;
use WebserviceSpecificManagementInterface;

class WebserviceSpecificManagementMoloniResource implements WebserviceSpecificManagementInterface
{
    // @codingStandardsIgnoreEnd

    /**
     * @var WebserviceOutputBuilder
     */
    protected $objOutput;
    protected $output;

    /**
     * @var WebserviceRequest
     */
    protected $wsObject;

    /**
     * Interface method
     *
     * @return $this
     */
    public function setObjectOutput(WebserviceOutputBuilderCore $obj)
    {
        $this->objOutput = $obj;

        return $this;
    }

    /**
     * Interface method
     *
     * @return WebserviceOutputBuilder
     */
    public function getObjectOutput()
    {
        return $this->objOutput;
    }

    /**
     * Interface method
     *
     * @return $this
     */
    public function setWsObject(WebserviceRequestCore $obj)
    {
        $this->wsObject = $obj;

        return $this;
    }

    /**
     * Interface method
     *
     * @return WebserviceRequest
     */
    public function getWsObject()
    {
        return $this->wsObject;
    }

    /**
     * Manages the incoming requests
     * Switches between operations
     */
    public function manage()
    {
        // get post params
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);

        // model needs to be Product and Tokens need to be valid
        if ($request['model'] !== 'Product' || General::staticCheckTokens() === false) {
            $this->output = 'Bad request.';

            return;
        }

        $variables = [
            'companyId' => (int) Company::get('company_id'),
            'productId' => (int) ($request['productId']),
        ];

        $moloniProduct = Products::queryProduct($variables);

        if (isset($moloniProduct['errors']) || empty($moloniProduct['data']['product']['data'])) {
            $this->output = 'Bad request.';

            return;
        }

        $moloniProduct = $moloniProduct['data']['product']['data'];

        switch ($request['operation']) {
            case 'create':
                $this->create($moloniProduct);
                break;
            case 'update':
                $this->update($moloniProduct);
                break;
            case 'stockChanged':
                // if the changed product was a variant (because stock changes appens at variant level)
                if (empty($moloniProduct['variants'])) {
                    $this->stockUpdate($moloniProduct);
                } else {
                    $this->stockUpdateVariants($moloniProduct);
                }
                break;
            default:
                $this->output = 'Bad request.';

                return;
        }
    }

    /**
     * Create new product
     *
     * @param $moloniProduct
     */
    public function create($moloniProduct)
    {
        if ((int) Settings::get('HooksAddProducts') !== 1) {
            return;
        }

        $psProductId = Product::getIdByReference($moloniProduct['reference']);

        if ($psProductId === false) {
            $psProduct = $this->setProduct($moloniProduct, 0);

            // variants need to be added after the parent is added
            // create variants if the moloni array has them
            if (!empty($moloniProduct['variants'])) {
                $this->setVariants($moloniProduct, $psProduct);
            }

            Log::writeLog('Product created in Prestashop: ' . $moloniProduct['reference']);
        } else {
            Log::writeLog('Product already exists in Prestashop: ' . $moloniProduct['reference']);
        }
    }

    /**
     * Update product
     *
     * @param $moloniProduct
     */
    public function update($moloniProduct)
    {
        if ((int) Settings::get('HooksUpdateProducts') !== 1) {
            return false;
        }

        $psProductId = Product::getIdByReference($moloniProduct['reference']);

        // to prevent infinite loops
        if (LogSync::wasSyncedRecently(1, $psProductId)) {
            Log::writeLog('Product has already been synced (moloni -> prestashop)');

            return false;
        }

        if ($psProductId > 0) {
            $psProduct = $this->setProduct($moloniProduct, $psProductId);

            if ((int) Settings::get('HooksVariantsUpdate') === 1) {
                if (!empty($moloniProduct['variants'])) {
                    $this->setVariants($moloniProduct, $psProduct);
                }
            }

            Log::writeLog('Product updated in Prestashop:' . $moloniProduct['reference']);
        } else {
            $this->create($moloniProduct);
            Log::writeLog('Product not found in Prestashop to update:' . $moloniProduct['reference']);
        }

        return true;
    }

    /**
     * Update product stock
     *
     * @param $moloniProduct
     */
    public function stockUpdate($moloniProduct)
    {
        if ((int) Settings::get('HooksUpdateStock') !== 1) {
            return;
        }

        $psProductId = Product::getIdByReference($moloniProduct['reference']);

        if ($psProductId > 0) {
            $currentStock = Product::getQuantity($psProductId);
            $newStock = $moloniProduct['stock'];

            if ((float) $currentStock === (float) $newStock) {
                Log::writeLog(
                    'Product with reference ' . $moloniProduct['reference'] .
                    ' already was up-to-date ' . $currentStock . ' | ' . $newStock
                );
            } else {
                StockAvailable::setQuantity($psProductId, null, $newStock);

                Log::writeLog(
                    'Product with reference ' . $moloniProduct['reference'] .
                    ' was updated from ' . $currentStock . ' to ' . $newStock
                );
            }
        } else {
            Log::writeLog('Product not found in Prestashop or without active stock:' . $moloniProduct['reference']);
        }
    }

    /**
     * Update variants stock
     *
     * @param $moloniProduct
     */
    public function stockUpdateVariants($moloniProduct)
    {
        if ((int) Settings::get('HooksUpdateStock') !== 1) {
            return;
        }

        $psProductId = Product::getIdByReference($moloniProduct['reference']);

        if ($psProductId > 0) {
            foreach ($moloniProduct['variants'] as $variant) {
                $combinationId = Combination::getIdByReference($psProductId, $variant['reference']);

                if ($combinationId !== false) {
                    $currentStock = (float) Product::getQuantity($psProductId, $combinationId);
                    $newStock = (float) $variant['stock'];

                    if ($currentStock === $newStock) {
                        Log::writeLog(
                            'Variant with reference ' . $variant['reference'] .
                            ' already was up-to-date ' . $currentStock . ' | ' . $newStock
                        );
                    } else {
                        Log::writeLog(
                            'Variant with reference ' . $variant['reference'] .
                            ' was updated from ' . $currentStock . ' to ' . $newStock
                        );
                    }

                    StockAvailable::setQuantity($psProductId, $combinationId, $variant['stock']);
                } else {
                    Log::writeLog('Variant not found in Prestashop: ' . $variant['reference']);
                }
            }
        } else {
            Log::writeLog('Product not found in Prestashop: ' . $moloniProduct['reference']);
        }
    }

    /**
     * Creates or updates a product
     *
     * @param $moloniProduct
     * @param $psId
     *
     * @return Product
     */
    public function setProduct($moloniProduct, $psId)
    {
        $settings = unserialize(Settings::get('SyncFields'));

        if ($psId === 0) {
            $psProduct = new Product();
        } else {
            $psProduct = new Product($psId);
        }

        if (in_array('Name', $settings, true) === true || empty($psProduct->name)) {
            $psProduct->name = $moloniProduct['name'];
        }

        $psProduct->reference = $moloniProduct['reference'];

        if (in_array('Price', $settings, true) === true) {
            if (Settings::get('Tax') === 'LetPresta') {
                $psProduct->price = $moloniProduct['price'];
            } else {
                $psProduct->price = $moloniProduct['priceWithTaxes'];
            }
        }

        if (in_array('Description', $settings, true) === true) {
            $psProduct->description = $moloniProduct['summary'];
            $psProduct->description_short = $moloniProduct['summary'];
        }

        if (in_array('Visibility', $settings, true) === true) {
            if ((int) $moloniProduct['visible'] === 1) {
                $psProduct->visibility = 'both';
            } else {
                $psProduct->visibility = 'none';
            }
        }

        if (in_array('Categories', $settings, true) === true) {
            // all categories ids in an array, ordered from lower to higher
            $categoriesIdArray = $this->setCategories($moloniProduct['productCategory']['productCategoryId']);
            // prestashop products have categories and 1 main category

            // im saving twice beacuse categories only save the second time for some reason
            $psProduct->addToCategories($categoriesIdArray);
            $psProduct->save();
            $psProduct->addToCategories($categoriesIdArray);

            // set product main category
            $psProduct->id_category_default = ($categoriesIdArray[0]);
        }

        $psProduct->save();

        if (in_array('Stock', $settings, true) === true
            && (bool) $moloniProduct['hasStock'] === true
            && empty($moloniProduct['variants']) === true) {
            StockAvailable::setQuantity($psProduct->id, null, $moloniProduct['stock']);
        }

        return $psProduct;
    }

    /**
     * Creates/updates product variations
     *
     * @param $moloniProduct
     * @param $psProduct Product
     */
    public function setVariants($moloniProduct, $psProduct)
    {
        $lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $psProduct->quantity = 0;
        $psProduct->save();

        $settings = unserialize(Settings::get('SyncFields'));

        // set this product attributes
        $this->setAttributes(self::getAttributes($moloniProduct));

        foreach ($moloniProduct['variants'] as $variation) {
            $combinationAttributes = [];

            foreach ($variation['propertyPairs'] as $value) {
                $combinationAttributes[] = self::getAttributeId(
                    $value['propertyValue']['value'],
                    self::getAttributeGroupId($value['property']['name'])
                );
            }

            // set price of this variation
            if (Settings::get('Tax') === 'LetPresta') {
                $price = $variation['price'] - $moloniProduct['price'];
            } else {
                $price = $variation['priceWithTaxes'] - $moloniProduct['priceWithTaxes'];
            }

            $combinationId = Combination::getIdByReference($psProduct->id, $variation['reference']);

            if ($combinationId === false) {// create combination
                $tempId = $psProduct->addAttribute(
                    $price,
                    0,
                    0,
                    0,
                    0,
                    $variation['reference'],
                    '',
                    false
                );

                $psProduct->addAttributeCombinaison($tempId, $combinationAttributes);

                $psProduct->save();

                if (in_array('Stock', $settings, true) === true && (bool) $variation['hasStock'] === true) {
                    StockAvailable::setQuantity(
                        $psProduct->id,
                        combination::getIdByReference($psProduct->id, $variation['reference']),
                        ['stock']
                    );
                }
            } else { // update existing combination
                if (in_array('Price', $settings, true) === true) {
                    $oldData = ($psProduct->getAttributeCombinationsById($combinationId, $lang))[0];

                    $psProduct->updateAttribute(
                        $combinationId,
                        $oldData['wholesale_price'],
                        $price,
                        $oldData['weight'],
                        null,
                        $oldData['ecotax'],
                        null,
                        $oldData['reference'],
                        $oldData['ean13'],
                        $oldData['default_on'],
                        $oldData['location'],
                        $oldData['upc'],
                        $oldData['minimal_quantity'],
                        $oldData['available_date'],
                        false
                    );
                }

                if (in_array('Stock', $settings, true) === true && (bool) $variation['hasStock'] === true) {
                    StockAvailable::setQuantity($psProduct->id, $combinationId, $variation['stock']);
                }
            }

            if ($combinationId !== false) {
                Log::writeLog('Variant updated in Prestashop: ' . $variation['reference']);
            } else {
                Log::writeLog('Variant Created in Prestashop: ' . $variation['reference']);
            }
        }
    }

    /**
     * Create the  attribute group and attributes necessary for the Moloni variants
     *
     * @param $attributes array [att1 => [prop1, prop3, prop3], att2 => [prop1]]
     *
     * @throws PrestaShopException
     */
    public function setAttributes($attributes)
    {
        $lang = (int) Configuration::get('PS_LANG_DEFAULT');

        foreach ($attributes as $name => $options) {
            $attributeGroupId = self::getAttributeGroupId($name);

            if ($attributeGroupId === false) {
                $attributeGroup = new AttributeGroup();
                $attributeGroup->name[$lang] = $name;
                $attributeGroup->public_name[$lang] = $name;
                $attributeGroup->group_type = 'select';
                $attributeGroup->is_color_group = false;

                $attributeGroup->save();
                $attributeGroupId = $attributeGroup->id;
            }

            foreach ($options as $option) {
                $attributeId = self::getAttributeId($option, $attributeGroupId);

                if ($attributeId === false) {
                    $attribute = new Attribute();
                    $attribute->name[$lang] = $option;
                    $attribute->id_attribute_group = $attributeGroupId;

                    $attribute->save();
                }
            }
        }
    }

    /**
     * Creates sets product categories
     *
     * @param $moloniCategoryId
     *
     * @return array
     */
    public function setCategories($moloniCategoryId)
    {
        $namesArray = self::getCategoriesFromMoloni($moloniCategoryId); // all names from category tree
        $lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $categoriesIds = [];
        // the root of all categories has id = 2
        $parentId = 2;
        foreach ($namesArray as $prodCat) {
            if (Category::searchByNameAndParentCategoryId($lang, $prodCat, $parentId) === false) {
                $category = new Category();
                $category->name = [1 => $prodCat];
                $category->id_parent = $parentId;
                $category->link_rewrite = [1 => Tools::str2url($prodCat)];
                $category->save();

                $parentId = $category->id;

                array_unshift($categoriesIds, $parentId);
            } else {
                $parentId = (Category::searchByNameAndParentCategoryId($lang, $prodCat, $parentId))['id_category'];

                array_unshift($categoriesIds, $parentId);
            }
        }

        return $categoriesIds;
    }

    /**
     * Interface method
     *
     * @return array|false|string
     */
    public function getContent()
    {
        return $this->objOutput->getObjectRender()->overrideContent($this->output);
    }

    // ///////////////////////// AUXILIARY METHODS ///////////////////////////

    /**
     * Returns product variants attributes
     * [att1 => [prop1, prop3, prop3], att2 => [prop1]]
     *
     * @param $moloniProduct
     *
     * @return array
     */
    public static function getAttributes($moloniProduct)
    {
        $attributes = [];

        foreach ($moloniProduct['variants'] as $variant) {
            foreach ($variant['propertyPairs'] as $property) {
                if (!in_array($property['propertyValue']['value'], $attributes[$property['property']['name']], true)) {
                    $attributes[$property['property']['name']][] = $property['propertyValue']['value'];
                }
            }
        }

        return $attributes;
    }

    /**
     * Gets categories tree from moloni product, bottom to top
     *
     * @param $moloniCategoryId
     *
     * @return array
     */
    public static function getCategoriesFromMoloni($moloniCategoryId)
    {
        $moloniId = $moloniCategoryId; // current category id
        $moloniCategoriesTree = [];
        $failsafe = 0; // we dont want the while loop to be stuck

        if ($moloniCategoryId === null) {
            return $moloniCategoriesTree; // can happen because product can have no category in moloni.es
        }

        do {
            $variables = [
                'companyId' => (int) Company::get('company_id'),
                'productCategoryId' => (int) $moloniId,
            ];

            $query = (apiCategories::queryProductCategory($variables))['data']['productCategory']['data'];

            array_unshift($moloniCategoriesTree, $query['name']); // order needs to be inverted

            if ($query['parent'] === null) {
                break; // break if category has no parent
            }

            $moloniId = $query['parent']['productCategoryId']; // next current id is this category parent

            ++$failsafe;
        } while ($failsafe < 100);

        return $moloniCategoriesTree; // returns the names of all categories (from this product only)
    }

    /**
     * Return the id of an attribute group if it exists, false if doesnt
     *
     * @param $name
     *
     * @return bool|mixed
     */
    public static function getAttributeGroupId($name)
    {
        $lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $arrayAttributesGroups = AttributeGroup::getAttributesGroups($lang);
        $idAttributeGroup = false;

        foreach ($arrayAttributesGroups as $attributeGroup) {
            if ($attributeGroup['name'] === $name) {
                $idAttributeGroup = $attributeGroup['id_attribute_group'];
                break;
            }
        }

        return $idAttributeGroup;
    }

    /**
     * Return the id of an attribute if it exists, false if doesnt
     *
     * @param $name
     * @param $idAttributeGroup
     *
     * @return bool|mixed
     */
    public static function getAttributeId($name, $idAttributeGroup)
    {
        $lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $arrayAttributes = Attribute::getAttributes($lang);
        $idAttribute = false;

        foreach ($arrayAttributes as $attributeGroup) {
            if ($attributeGroup['name'] === $name && $attributeGroup['id_attribute_group'] === $idAttributeGroup) {
                $idAttribute = $attributeGroup['id_attribute'];
                break;
            }
        }

        return $idAttribute;
    }
}
