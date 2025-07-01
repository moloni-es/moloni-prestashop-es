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

namespace Moloni\Activators;

use Moloni\Entity\MoloniApp;
use Moloni\Repository\MoloniAppRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Remove extends ActivatorAbstract
{
    /**
     * Installer constructor.
     */
    public function __construct(\CoreModule $module)
    {
        $this->module = $module;
    }

    /**
     * Uninstall plugin
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return $this->destroyCommon();
    }

    /**
     * Disable plugin
     *
     * @return bool
     */
    public function disable(): bool
    {
        return $this->destroyCommon();
    }

    //        PRIVATES        //

    private function destroyCommon(): bool
    {
        $this->removeHooks();
        $this->removeLogin();

        return true;
    }

    //        AUXILIARY       //

    private function removeLogin(): void
    {
        try {
            /** @var MoloniAppRepository $repository */
            $repository = $this
                ->module
                ->get('doctrine')
                ->getRepository(MoloniApp::class);

            $repository->deleteApp();
        } catch (\Exception $e) {
            // No need to catch
        }
    }

    private function removeHooks(): void
    {
        foreach ($this->hooks as $hookName) {
            $this->module->unregisterHook($hookName);
        }
    }
}
