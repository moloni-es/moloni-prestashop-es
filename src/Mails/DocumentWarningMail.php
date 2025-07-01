<?php

/**
 * 2025 - Moloni.com
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

namespace Moloni\Mails;

use Moloni\MoloniContext;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DocumentWarningMail extends SendMail
{
    public function handle(): void
    {
        $orderId = $this->data['order_id'] ?? 0;

        \Mail::Send(
            (int) \Configuration::get('PS_LANG_DEFAULT'), // default language id
            'document_warning', // email template file to be used
            $this->subject, // email subject
            array_merge(['{order_id}' => $orderId], $this->getCommonVars()),
            $this->email, // receiver email address
            null, // receiver name
            null, // from email address
            null,  // from name
            null, // file attachment
            null, // mode smtp
            MoloniContext::instance()->getModuleDir() . 'mails' // custom template path
        );
    }
}
