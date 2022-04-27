<?php

namespace Moloni\Hooks;

use Configuration;
use Moloni\Controllers\General;
use Moloni\Controllers\Models\Log;
use Moloni\Controllers\Models\LogSync;
use Moloni\Controllers\Models\Product;
use Moloni\Helpers\Settings;
use PrestaShopDatabaseException;
use PrestaShopException;

class ProductUpdate
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
        if (((int) Settings::get('UpdateArtigos') === 1)) {
            return true;
        }

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
