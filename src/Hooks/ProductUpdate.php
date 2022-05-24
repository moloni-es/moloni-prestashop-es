<?php

namespace Moloni\Hooks;

class ProductUpdate extends AbstractHookAction
{
    private $productId;

    public function __construct(int $productId)
    {
        parent::__construct();

        $this->productId = $productId;
    }

    public function handle(): void
    {
        if (!$this->isAuthenticated) {
            return;
        }

        $this->productId = $this->productId ?: 0;
        // todo: do something
    }
}
