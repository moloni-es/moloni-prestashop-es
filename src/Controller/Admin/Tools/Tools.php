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

use Moloni\Controller\Admin\Controller;
use Moloni\Traits\MoloniCategoriesTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Tools extends Controller
{
    use MoloniCategoriesTrait;

    public function home(Request $request): Response
    {
        return $this->render('@Modules/molonies/views/templates/admin/settings/Tools.twig');
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

    public function discardOrders(Request $request): RedirectResponse
    {
        // todo: this

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

    public function logout(Request $request): RedirectResponse
    {
        // todo: this

        return $this->redirectToLogin();
    }
}
