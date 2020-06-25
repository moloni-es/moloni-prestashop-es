<?php

namespace Moloni\ES\Hooks;

use Configuration;
use Moloni\ES\Controllers\General;
use Moloni\ES\Controllers\Models\Log;
use Moloni\ES\Controllers\Models\Product;
use PrestaShopBundle\Translation\DataCollectorTranslator;
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
     * @param DataCollectorTranslator $translator translator component
     */
    public function __construct(DataCollectorTranslator $translator)
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
        $productPS = new \PrestaShop\PrestaShop\Adapter\Entity\Product(
            $productId,
            1,
            Configuration::get('PS_LANG_DEFAULT')
        );

        if ((new General())->checkTokens() != true) {
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
