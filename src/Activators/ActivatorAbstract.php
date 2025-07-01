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

declare(strict_types=1);

namespace Moloni\Activators;

use Moloni\Configurations;

class ActivatorAbstract
{
    /**
     * The module data
     *
     * @var \CoreModule
     */
    protected $module;

    /**
     * Hooks list
     *
     * @var string[]
     */
    protected $hooks = [
        'actionAdminControllerSetMedia',
        'actionOrderStatusUpdate',
        'actionProductAdd',
        'actionProductUpdate',
        'actionUpdateQuantity',
        'actionGetAdminOrderButtons',
        'addWebserviceResources',
        'actionAdminProductsControllerSaveBefore',
    ];

    /**
     * Plugin tabs list
     *
     * @var array[]
     */
    protected $tabs = [
        [
            'class_name' => 'Moloni',
            'parent_class_name' => 'SELL',
            'name' => [
                'en' => 'Moloni Spain',
                'es' => 'Moloni España',
                'pt' => 'Moloni Espanha',
            ],
            'wording' => 'Moloni Spain',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => 'logo',
        ],
        [
            'class_name' => 'MoloniOrders',
            'parent_class_name' => 'Moloni',
            'name' => [
                'en' => 'Orders',
                'es' => 'Pedidos pendientes',
                'pt' => 'Encomendas',
            ],
            'wording' => 'Orders',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => '',
        ],
        [
            'class_name' => 'MoloniDocuments',
            'parent_class_name' => 'Moloni',
            'name' => [
                'en' => 'Documents',
                'es' => 'Documentos creados',
                'pt' => 'Documentos',
            ],
            'wording' => 'Documents',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => '',
        ],
        [
            'class_name' => 'MoloniSettings',
            'parent_class_name' => 'Moloni',
            'name' => [
                'en' => 'Settings',
                'es' => 'Configuraciones',
                'pt' => 'Configurações',
            ],
            'wording' => 'Settings',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => '',
        ],
        [
            'class_name' => 'MoloniTools',
            'parent_class_name' => 'Moloni',
            'name' => [
                'en' => 'Tools',
                'es' => 'Herramientas',
                'pt' => 'Ferramentas',
            ],
            'wording' => 'Tools',
            'wording_domain' => 'Modules.Molonies.Admin',
            'icon' => '',
        ],
    ];
}
