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

namespace Moloni\EventListener;

use Moloni\Api\MoloniApi;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Controller\Admin\MoloniControllerInterface;
use Moloni\Enums\MoloniRoutes;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class AuthenticationListener
{
    public function onKernelController(FilterControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller) && $controller[0] instanceof MoloniControllerInterface) {
            $route = $event->getRequest()->get('_route');

            /** @var MoloniController $actionController */
            $actionController = $controller[0];

            if (!MoloniApi::hasValidCompany()) {
                if (MoloniApi::hasValidAuthentication() && !MoloniRoutes::isPartiallyAuthenticatedRoute($route)) {
                    $event->setController(function () use ($actionController) {
                        return $actionController->redirectToCompanySelect();
                    });

                    return;
                }
            }

            if (MoloniRoutes::isFullyAuthenticatedRoute($route) && !MoloniApi::hasValidAuthentication()) {
                $event->setController(function () use ($actionController) {
                    return $actionController->redirectToLogin();
                });

                return;
            }

            if (MoloniRoutes::isNonAuthenticatedRoute($route) && MoloniApi::hasValidAuthentication()) {
                $event->setController(function () use ($actionController) {
                    return $actionController->redirectToOrders();
                });
            }
        }
    }
}
