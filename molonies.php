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

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

include __DIR__ . '/CoreModule.php';

class MoloniEs extends CoreModule
{
    /**
     * Molonies constructor.
     */
    public function __construct()
    {
        $this->name = 'molonies';
        $this->tab = 'administration';
        $this->author = 'Moloni';

        $this->need_instance = 1;
        $this->version = '#VERSION#';
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => '8.1.3'];

        parent::__construct();

        $this->displayName = $this->trans('Moloni Spain', [], 'Modules.Molonies.Core');
        $this->description = $this->trans(
            'Automatic document creation with real time stock synchronization and powerful sales analysis.',
            [],
            'Modules.Molonies.Core'
        );
        $this->confirmUninstall = $this->trans(
            'Are you sure you want to uninstall this module?',
            [],
            'Modules.Molonies.Core'
        );

        $this->autoload();
    }
}
