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

namespace Moloni\Hooks;

use Moloni\Api\MoloniApi;
use Moloni\Entity\MoloniOrderDocuments;
use Moloni\Enums\MoloniRoutes;
use Moloni\Repository\MoloniOrderDocumentsRepository;
use PrestaShopBundle\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ManagerRegistry as LegacyManagerRegistry;
use PrestaShop\PrestaShop\Core\Exception\TypeException;
use PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton;
use PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection;

class AdminOrderButtons extends AbstractHookAction
{
    private $router;
    private $translator;

    /**
     * @var int
     */
    private $orderId;
    /**
     * @var ActionsBarButtonsCollection
     */
    private $actionBar;
    /**
     * @var MoloniOrderDocumentsRepository
     */
    private $moloniDocumentsRepository;

    /**
     * Construct
     *
     * @param array $params
     * @param Router $router
     * @param ManagerRegistry|LegacyManagerRegistry $doctrine
     * @param TranslatorInterface $translator
     *
     * @throws TypeException
     */
    public function __construct(array &$params, Router $router, $doctrine, TranslatorInterface  $translator)
    {
        $this->router = $router;
        $this->translator = $translator;

        $this->actionBar = $params['actions_bar_buttons_collection'];
        $this->orderId = (int)$params['id_order'];
        $this->moloniDocumentsRepository = $doctrine->getRepository(MoloniOrderDocuments::class);

        $this->handle();
    }

    /**
     * Handler
     *
     * @throws TypeException
     */
    private function handle(): void
    {
        if (!$this->shouldExecuteHandle()) {
            return;
        }

        /** @var MoloniOrderDocuments|null $document */
        $document = $this->moloniDocumentsRepository->findOneBy(['orderId' => $this->orderId]);

        if ($document === null) {
            $this->addCreateButton();
        } elseif ($document->getDocumentId() > 0) {
            $this->addViewButton($document->getDocumentId());
        }
    }

    /**
     * Add view document button
     *
     * @param int $documentId
     *
     * @throws TypeException
     */
    private function addViewButton(int $documentId): void
    {
        $href = $this->router->generate(MoloniRoutes::DOCUMENTS_VIEW, [
            'document_id' => $documentId
        ]);

        $title = $this->getMoloniLogo();
        $title .= $this->translator->trans('View document', [], 'Modules.Molonies.Common');

        $this->actionBar->add(
            new ActionsBarButton(
                'btn-secondary',
                [
                    'href' => $href, 'target' => '_blank'
                ],
                $title
            )
        );
    }

    /**
     * Add create document button
     *
     * @throws TypeException
     */
    private function addCreateButton(): void
    {
        $href = $this->router->generate(MoloniRoutes::ORDERS_CREATE, [
            'order_id' => $this->orderId,
            'from_order_page' => true,
        ]);

        $title = $this->getMoloniLogo();
        $title .= $this->translator->trans('Create document', [], 'Modules.Molonies.Common');

        $this->actionBar->add(
            new ActionsBarButton(
                'btn-secondary',
                [
                    'href' => $href
                ],
                $title
            )
        );
    }

    private function getMoloniLogo(): string
    {
        return '<i class="material-icons mi-logo">logo</i> ';
    }

    private function shouldExecuteHandle(): bool
    {
        return $this->isAuthenticated() && MoloniApi::hasValidCompany();
    }
}
