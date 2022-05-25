<?php

namespace Moloni\Hooks;

use Moloni\Builders\MoloniProductFromId;
use Moloni\Enums\Boolean;
use Moloni\Exceptions\Product\MoloniProductException;
use Moloni\Helpers\Settings;

class ProductUpdate extends AbstractHookAction
{
    private $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    public function handle(): void
    {
        if (!$this->shouldExecuteHandle()) {
            return;
        }

        try {
            $productBuilder = new MoloniProductFromId($this->productId);
            $productBuilder->search();

            if ($productBuilder->getProductId() !== 0) {
                $productBuilder
                    ->update()
                    ->updateStock();
            } elseif ((int)Settings::get('addProductsToMoloni') === Boolean::YES) {
                $productBuilder->insert();
            }
        } catch (MoloniProductException $e) {
            // todo: write log?
        }
    }

    private function shouldExecuteHandle(): bool
    {
        if ($this->productId < 1) {
            return false;
        }

        if ((int)Settings::get('updateProductsToMoloni') === Boolean::YES) {
            return false;
        }

        // todo: check for ping pong effect

        return $this->isAuthenticated();
    }
}
