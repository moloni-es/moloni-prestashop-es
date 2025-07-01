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

abstract class SendMail
{
    protected $email;
    protected $data;
    protected $subject;

    /**
     * Construct
     *
     * @param string|null $email
     * @param array|null $data
     */
    public function __construct(?string $email = '', ?array $data = [])
    {
        $this->email = $email;
        $this->data = $data;

        $this->subject = 'Prestashop - Moloni plugin';
    }

    protected function getCommonVars(): array
    {
        return [
            '{moloni_url}' => MoloniContext::instance()->configs()->get('home_page'),
            '{moloni_logo_url}' => MoloniContext::instance()->getImgPath() . 'logo_white.png',
            '{year}' => date('Y'),
        ];
    }
}
