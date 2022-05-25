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

use OrderState;
use Moloni\Exceptions\Document\MoloniDocumentException;
use Moloni\Exceptions\Document\MoloniDocumentWarning;
use Moloni\Exceptions\MoloniException;
use Moloni\Enums\Boolean;
use Moloni\Helpers\Settings;
use Moloni\Actions\Orders\OrderCreateDocument;
use Doctrine\Persistence\ObjectManager;
use PrestaShopDatabaseException;
use PrestaShopException;

class OrderStatusUpdate extends AbstractHookAction
{
    private $orderId;

    private $newOrderStatus;

    private $entityManager;

    public function __construct(int $orderId, OrderState $newOrderStatus, ObjectManager $entityManager)
    {
        $this->orderId = $orderId;
        $this->newOrderStatus = $newOrderStatus;
        $this->entityManager = $entityManager;
    }

    public function handle(): void
    {
        if (!$this->shouldExecuteHandle()) {
            return;
        }

        try {
            $action = new OrderCreateDocument($this->orderId, $this->entityManager);
            $action->handle();

            $msg = 'Document created successfully (automatic) ';
        } catch (MoloniDocumentWarning $e) {

        } catch (MoloniDocumentException $e) {

        } catch (MoloniException $e) {

        } catch (PrestaShopDatabaseException|PrestaShopException $e) {

        }
    }

    private function shouldExecuteHandle(): bool
    {
        if ($this->orderId < 1) {
            return false;
        }

        if ((int)Settings::get('automaticDocuments') === Boolean::NO) {
            return false;
        }

        $orderStatusToShow = Settings::get('orderStatusToShow');

        if ($orderStatusToShow === null) {
            if ((int)$this->newOrderStatus->paid === Boolean::NO) {
                return false;
            }
        } elseif (!in_array($this->newOrderStatus->id, $orderStatusToShow, true)) {
            return false;
        }

        return $this->isAuthenticated();
    }
}
