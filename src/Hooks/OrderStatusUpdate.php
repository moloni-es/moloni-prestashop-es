<?php

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
