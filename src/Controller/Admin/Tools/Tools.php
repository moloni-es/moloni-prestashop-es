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

namespace Moloni\Controller\Admin\Tools;

use Moloni\Actions\Tools\WebhookCreate;
use Moloni\Actions\Tools\WebhookDeleteAll;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\MoloniException;
use Moloni\Helpers\Settings;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Tools extends MoloniController
{
    public function home(Request $request): Response
    {
        return $this->render('@Modules/molonies/views/templates/admin/tools/Tools.twig');
    }

    public function importProducts(Request $request): RedirectResponse
    {
        // todo: this

        return $this->redirectToTools();
    }

    public function importCategories(Request $request): RedirectResponse
    {
        // todo: this

        return $this->redirectToTools();
    }

    public function syncStocks(Request $request): RedirectResponse
    {
        // todo: this

        return $this->redirectToTools();
    }

    public function reinstallHooks(Request $request): RedirectResponse
    {
        try {
            (new WebhookDeleteAll())->handle();
            $action = new WebhookCreate();

            if (Settings::get('syncStockToPrestashop') === Boolean::YES) {
                $action->handle('Product', 'stockChanged');
            }

            if (Settings::get('addProductsToPrestashop') === Boolean::YES) {
                $action->handle('Product', 'create');
            }

            if (Settings::get('updateProductsToPrestashop') === Boolean::YES) {
                $action->handle('Product', 'update');
            }

            $msg = $this->trans('Webhooks reinstall was successful', 'Modules.Molonies.Common');
            $this->addSuccessMessage($msg);
        } catch (MoloniException $e) {
            $msg = $this->trans($e->getMessage(), 'Modules.Molonies.Errors', $e->getIdentifiers());
            $this->addErrorMessage($msg, $e->getData());
        }

        return $this->redirectToTools();
    }

    public function openLogs(Request $request): Response
    {
        // todo: this

        return $this->render(
            '@Modules/molonies/views/templates/admin/settings/ConsultLogs.twig',
            [
                'logs' => '',
            ]
        );
    }

    public function deleteLogs(Request $request): RedirectResponse
    {
        // todo: this

        return $this->redirectToTools();
    }

    public function logout(): RedirectResponse
    {
        return $this->redirectToLogin();
    }
}
