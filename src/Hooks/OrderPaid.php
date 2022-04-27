<?php

namespace Moloni\Hooks;

use Moloni\Controllers\General;
use Moloni\Helpers\Documents as modelDocuments;
use Moloni\Helpers\Log;
use Moloni\Helpers\Settings;

class OrderPaid
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
        if (((int) Settings::get('CreateAuto') === 1)) {
            return true;
        }

        // check if the settings have been set
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
