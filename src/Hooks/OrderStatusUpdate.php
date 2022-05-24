<?php

namespace Moloni\Hooks;

use OrderState;

class OrderStatusUpdate extends AbstractHookAction
{
    private $productId;

    private $newOrderStatusId;

    public function __construct(int $productId, OrderState $newOrderStatusId)
    {
        parent::__construct();

        $this->productId = $productId;
        $this->newOrderStatusId = $newOrderStatusId;
    }

    public function handle(): void
    {
        if ($this->isAuthenticated) {
            return;
        }

        $this->productId = $this->productId ?: 0;
        $this->newOrderStatusId = $this->newOrderStatusId ?: new OrderState();
        // todo: do something
    }
}
