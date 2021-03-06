<?php

namespace Moloni\ES\Hooks;

use Moloni\ES\Controllers\General;
use Moloni\ES\Controllers\Models\Documents as modelDocuments;
use Moloni\ES\Controllers\Models\Log;
use Moloni\ES\Controllers\Models\Settings;

class PaymentConfirmation
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
     * Creates an document
     *
     * @param $orderId
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookActionPaymentConfirmation($orderId)
    {
        //check if the settings have been set
        if (!Settings::get('Type')) {
            Log::writeLog('Pls set document type in settings!!');

            return false;
        }

        if (General::staticCheckTokens() !== true) {
            Log::writeLog('Tokens are not valid. Cant create document!!');

            return false;
        }

        $newDocument = new modelDocuments($orderId, Settings::get('Type'), $this->translator);

        if ($newDocument->init() === true) {
            $newDocument->create();
        }

        return true;
    }
}
