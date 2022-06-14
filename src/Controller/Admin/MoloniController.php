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

use Moloni\Entity\MoloniApp;
use Moloni\Enums\MoloniRoutes;
use Moloni\Repository\MoloniAppRepository;
use Moloni\Services\MoloniContext;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class MoloniController extends FrameworkBundleAdminController implements MoloniControllerInterface
{
    /**
     * Moloni plugin context
     *
     * @var MoloniContext
     */
    protected $moloniContext;

    /**
     * Start our service
     *
     * @param ContainerInterface|null $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        parent::setContainer($container);

        $this->moloniContext = $this->get('moloni.services.context');
    }

    //          Privates          //

    /**
     * Creates payload message to show user
     *
     * @param array $errors
     *
     * @return string
     */
    private function getErrorPayload(array $errors): string
    {
        $msg = $this->trans('Click for more information', 'Modules.Molonies.Errors');

        $error = ' </br>';
        $error .= '<a onclick="$(\'#toggleDiv\').toggle(200);" href="#">' . $msg . '</a>';
        $error .= '</br>';

        $error .= '<div style="display: none;" id="toggleDiv">';

        foreach ($errors as $key => $value) {
            $error .= '<br>';
            $error .= '    <b>' . $key . ': </b>';
            $error .= '    <pre>' . print_r($value, true) . '</pre>';
            $error .= '</br>';
        }

        $error .= '</div>';

        return $error;
    }

    //          Messages          //

    /**
     * Adds message to the user
     *
     * @param string $message
     * @param string $type
     *
     * @return bool
     */
    private function addFlashMessage(string $message, string $type): bool
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
    protected function addSuccessMessage(string $message): bool
    {
        return $this->addFlashMessage($message, 'success');
    }

    /**
     * Adds warning message to the user
     *
     * @param string $message
     * @param array|null $error Error data
     *
     * @return bool
     */
    protected function addWarningMessage(string $message, ?array $error = []): bool
    {
        if (!empty($error)) {
            $message .= $this->getErrorPayload($error);
        }

        return $this->addFlashMessage($message, 'warning');
    }

    /**
     * Adds error message to the user
     *
     * @param string $message Error message
     * @param array|null $error Error data
     *
     * @return bool
     */
    protected function addErrorMessage(string $message, ?array $error = []): bool
    {
        if (!empty($error)) {
            $message .= $this->getErrorPayload($error);
        }

        return $this->addFlashMessage($message, 'error');
    }

    //          Redirects          //

    /**
     * Redirect to log in page
     *
     * @return RedirectResponse
     */
    public function redirectToLogin(): RedirectResponse
    {
        /** @var MoloniAppRepository $repository */
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(MoloniApp::class);

        $repository->deleteApp();

        return $this->redirectToRoute(MoloniRoutes::LOGIN);
    }

    /**
     * Redirect to company select page
     *
     * @return RedirectResponse
     */
    public function redirectToCompanySelect(): RedirectResponse
    {
        return $this->redirectToRoute(MoloniRoutes::LOGIN_COMPANY_SELECT);
    }

    /**
     * Redirect to Orders page
     *
     * @param int|null $page
     *
     * @return RedirectResponse
     */
    public function redirectToOrders(?int $page = 1): RedirectResponse
    {
        return $this->redirectToRoute(MoloniRoutes::ORDERS, ['page' => $page]);
    }

    /**
     * Redirect to document settings page
     *
     * @param int|null $page
     *
     * @return RedirectResponse
     */
    protected function redirectToDocuments(?int $page = 1): RedirectResponse
    {
        return $this->redirectToRoute(MoloniRoutes::DOCUMENTS, ['page' => $page]);
    }

    /**
     * Redirect to Tool settings page
     *
     * @return RedirectResponse
     */
    protected function redirectToTools(): RedirectResponse
    {
        return $this->redirectToRoute(MoloniRoutes::TOOLS);
    }

    /**
     * Redirect to log's page
     *
     * @return RedirectResponse
     */
    protected function redirectToLogs(): RedirectResponse
    {
        return $this->redirectToRoute(MoloniRoutes::TOOLS_OPEN_LOGS);
    }

    /**
     * Redirect to Settings Page
     *
     * @return RedirectResponse
     */
    protected function redirectToSettings(): RedirectResponse
    {
        return $this->redirectToRoute(MoloniRoutes::SETTINGS);
    }
}
