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

use Exception;
use Tools as PrestashopTools;
use Moloni\Actions\Exports\ExportProductsToMoloni;
use Moloni\Actions\Exports\ExportStocksToMoloni;
use Moloni\Actions\Imports\ImportProductsFromMoloni;
use Moloni\Actions\Imports\ImportStockChangesFromMoloni;
use Moloni\Actions\Tools\LogsListDetails;
use Moloni\Actions\Tools\WebhookCreate;
use Moloni\Actions\Tools\WebhookDeleteAll;
use Moloni\Controller\Admin\MoloniController;
use Moloni\Entity\MoloniLogs;
use Moloni\Enums\Boolean;
use Moloni\Enums\LogLevel;
use Moloni\Enums\MoloniRoutes;
use Moloni\Exceptions\MoloniException;
use Moloni\Repository\MoloniLogsRepository;
use Moloni\Tools\Settings;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Tools extends MoloniController
{
    public function home(): Response
    {
        return $this->render(
            '@Modules/molonies/views/templates/admin/tools/Tools.twig',
            [
                'companyName' => Settings::get('companyName'),
                'importProductsRoute' => MoloniRoutes::TOOLS_IMPORT_PRODUCTS,
                'importStocksRoute' => MoloniRoutes::TOOLS_IMPORT_STOCKS,
                'exportProductsRoute' => MoloniRoutes::TOOLS_EXPORT_PRODUCTS,
                'exportStocksRoute' => MoloniRoutes::TOOLS_EXPORT_STOCKS,
                'reinstallHooksRoute' => MoloniRoutes::TOOLS_REINSTALL_HOOKS,
                'openLogsRoute' => MoloniRoutes::TOOLS_OPEN_LOGS,
                'logoutRoute' => MoloniRoutes::TOOLS_LOGOUT,
            ]
        );
    }

    public function importProducts(): Response
    {
        $page = (int)PrestashopTools::getValue('page', 1);

        $response = [
            'valid' => true,
            'post' => [
                'page' => $page
            ]
        ];

        $tool = new ImportProductsFromMoloni($page);
        $tool->handle();

        $response['hasMore'] = $tool->getHasMore();

        $response['overlayContent'] = $this->renderView(
            '@Modules/molonies/views/templates/admin/tools/overlays/blocks/ProductImportContent.twig',
            [
                'hasMore' => $tool->getHasMore(),
                'totalResults' => $tool->getTotalResults(),
                'currentPercentage' => $tool->getCurrentPercentage(),
            ]
        );

        return new Response(json_encode($response));
    }

    public function importStocks(): Response
    {
        $page = (int)PrestashopTools::getValue('page', 1);

        $response = [
            'valid' => true,
            'post' => [
                'page' => $page
            ]
        ];

        $tool = new ImportStockChangesFromMoloni($page);
        $tool->handle();

        $response['hasMore'] = $tool->getHasMore();

        $response['overlayContent'] = $this->renderView(
            '@Modules/molonies/views/templates/admin/tools/overlays/blocks/ProductImportContent.twig',
            [
                'hasMore' => $tool->getHasMore(),
                'totalResults' => $tool->getTotalResults(),
                'currentPercentage' => $tool->getCurrentPercentage(),
            ]
        );

        return new Response(json_encode($response));
    }

    public function exportProducts(): Response
    {
        $page = (int)PrestashopTools::getValue('page', 1);

        $response = [
            'valid' => true,
            'post' => [
                'page' => $page
            ]
        ];

        $tool = new ExportProductsToMoloni($page, $this->getContextLangId());
        $tool->handle();

        $response['hasMore'] = $tool->getHasMore();

        $response['overlayContent'] = $this->renderView(
            '@Modules/molonies/views/templates/admin/tools/overlays/blocks/ProductExportContent.twig',
            [
                'hasMore' => $tool->getHasMore(),
                'processedProducts' => $tool->getProcessedProducts(),
            ]
        );

        return new Response(json_encode($response));
    }

    public function exportStocks(): Response
    {
        $page = (int)PrestashopTools::getValue('page', 1);

        $response = [
            'valid' => true,
            'post' => [
                'page' => $page
            ]
        ];

        $tool = new ExportStocksToMoloni($page, $this->getContextLangId());
        $tool->handle();

        $response['hasMore'] = $tool->getHasMore();

        $response['overlayContent'] = $this->renderView(
            '@Modules/molonies/views/templates/admin/tools/overlays/blocks/ProductExportContent.twig',
            [
                'hasMore' => $tool->getHasMore(),
                'processedProducts' => $tool->getProcessedProducts(),
            ]
        );

        return new Response(json_encode($response));
    }

    public function reinstallHooks(): RedirectResponse
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

    public function openLogs(): Response
    {
        $page = (int)PrestashopTools::getValue('page', 1);
        $filters = PrestashopTools::getValue('filters', []);

        $logs = $paginator = [];

        /** @var MoloniLogsRepository $moloniLogsRepository */
        $moloniLogsRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(MoloniLogs::class);

        try {
            ['logs' => $logs, 'paginator' => $paginator] = $moloniLogsRepository->getAllPaginated($page, $filters);
        } catch (Exception $e) {
            $msg = $this->trans('Error fetching logs list', 'Modules.Molonies.Errors');

            $this->addErrorMessage($msg);
        }

        $logs = (new LogsListDetails($logs))->handle();

        return $this->render(
            '@Modules/molonies/views/templates/admin/logs/Logs.twig',
            [
                'logsArray' => $logs,
                'logsLevelsArray' => LogLevel::getLogLevels(),
                'filters' => $filters,
                'paginator' => $paginator,
                'companyName' => Settings::get('companyName'),
                'toolsRoute' => MoloniRoutes::TOOLS,
                'deleteLogsRoute' => MoloniRoutes::TOOLS_DELETE_LOGS,
                'thisRoute' => MoloniRoutes::TOOLS_OPEN_LOGS,
            ]
        );
    }

    public function deleteLogs(): RedirectResponse
    {
        /** @var MoloniLogsRepository $moloniLogsRepository */
        $moloniLogsRepository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(MoloniLogs::class);

        $moloniLogsRepository->deleteOlderLogs();

        $msg = $this->trans('Older logs deleted', 'Modules.Molonies.Common');
        $this->addSuccessMessage($msg);

        return $this->redirectToLogs();
    }

    public function logout(): RedirectResponse
    {
        return $this->redirectToLogin();
    }
}
