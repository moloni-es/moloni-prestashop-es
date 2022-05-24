<?php

namespace Moloni\Hooks;

use Moloni\Api\MoloniApi;

abstract class AbstractHookAction
{
    protected $isAuthenticated = false;

    public function __construct()
    {
        if (MoloniApi::hasValidAuthentication()) {
            $this->isAuthenticated = true;
        }
    }
}
