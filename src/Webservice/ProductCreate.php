<?php

namespace Moloni\Webservice;

class ProductCreate extends AbstractWebserviceAction
{
    public function handle(int $productId): void
    {
        if (!$this->isAuthenticated) {
            return;
        }

        // todo: stuff
    }
}
