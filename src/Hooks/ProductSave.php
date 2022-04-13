<?php

namespace Moloni\ES\Hooks;

use Configuration;
use Moloni\ES\Controllers\General;
use Moloni\ES\Controllers\Models\Log;
use Moloni\ES\Controllers\Models\LogSync;
use Moloni\ES\Controllers\Models\Product;
use PrestaShopDatabaseException;
use PrestaShopException;

class ProductSave
{
    /**
     * translator component
     */
    public $translator;

    /**
     * ProductSave constructor.
     *
     * @param $translator
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Called after creating or updating a product
     *
     * @param $productId
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionProductSave($productId)
    {
        // to prevent infinite loops
        if (LogSync::wasSyncedRecently(1, $productId) === true) {
            Log::writeLog('Product has already been synced (prestashop -> moloni)');

            return false;
        }

        $productPS = new \PrestaShop\PrestaShop\Adapter\Entity\Product(
            $productId,
            1,
            Configuration::get('PS_LANG_DEFAULT')
        );

        if (General::staticCheckTokens() !== true) {
            Log::writeLog('Tokens are not valid. Cant create document!!');

            return false;
        }

        $product = new Product($productPS, $this->translator);

        if ($product->init() == true) {
            $product->create();
        }

        return true;
    }
}
