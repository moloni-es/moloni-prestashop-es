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

namespace Moloni\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class Controller extends FrameworkBundleAdminController
{
    /**
     * Adds message to the user
     *
     * @param string $message
     * @param string $type
     *
     * @return bool
     */
    private function addMessage(string $message,string $type): bool
    {
        if (empty($message) || empty($type)) {
            return false;
        }

        $this->addFlash($type, $message);

        return true;
    }

    /**
     * Adds success message to the user
     *
     * @param string $message
     *
     * @return bool
     */
    protected function addSuccess(string $message): bool
    {
        return $this->addMessage($message,'success');
    }

    /**
     * Adds warning message to the user
     *
     * @param string $message
     *
     * @return bool
     */
    protected function addWarning(string $message): bool
    {
        return $this->addMessage($message,'warning');
    }

    /**
     * Adds error message to the user
     *
     * @param string $message
     *
     * @return bool
     */
    protected function addError(string $message): bool
    {
        return $this->addMessage($message,'error');
    }

    /**
     * Redirect to Log in index Page
     *
     * @return RedirectResponse
     */
    protected function redirectToLogin(): RedirectResponse
    {
        return $this->redirectToRoute('moloni_es_login_home');
    }

    /**
     * Redirect to company select page
     *
     * @return RedirectResponse
     */
    protected function redirectToCompanySelect(): RedirectResponse
    {
        return $this->redirectToRoute('moloni_es_login_company_select');
    }

    /**
     * Redirect to document settings page
     *
     * @return RedirectResponse
     */
    protected function redirectToDocuments(): RedirectResponse
    {
        return $this->redirectToRoute('moloni_es_documents_home');
    }

    /**
     * Redirect to Orders page
     *
     * @return RedirectResponse
     */
    protected function redirectToOrders(): RedirectResponse
    {
        return $this->redirectToRoute('moloni_es_orders_home');
    }

    /**
     * Redirect to Tool settings page
     *
     * @return RedirectResponse
     */
    protected function redirectToTools(): RedirectResponse
    {
        return $this->redirectToRoute('moloni_es_tools_home');
    }

    /**
     * Redirect to Settings Products page
     *
     * @return RedirectResponse
     */
    protected function redirectToSettingsProducts(): RedirectResponse
    {
        return $this->redirectToRoute('moloni_es_settings_products');
    }

    /**
     * Redirect to Index Settings Page
     *
     * @return RedirectResponse
     */
    protected function redirectToSettingsDocuments(): RedirectResponse
    {
        return $this->redirectToRoute('moloni_es_settings_documents');
    }

    /**
     * Redirect to Automation settings page
     *
     * @return RedirectResponse
     */
    protected function redirectToSettingsAutomation(): RedirectResponse
    {
        return $this->redirectToRoute('moloni_es_settings_automation');
    }
}
