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

namespace Moloni\Controller\Admin\Settings;

use Moloni\Controller\Admin\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Documents extends Controller
{
    public function home(Request $request): Response
    {
        // todo: this

        $form = $this->createFormBuilder()
            ->getForm();

        return $this->render(
            '@Modules/molonies/views/templates/admin/settings/Documents.twig',
            [
                'settingsForm' => $form->createView(),
                'tabActive' => 'index',
            ]
        );
    }

    public function save(Request $request): RedirectResponse
    {
        // todo: this

        return $this->redirectToSettingsDocuments();
    }
}
