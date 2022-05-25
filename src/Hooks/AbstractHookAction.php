<?php

namespace Moloni\Hooks;

use Moloni\Api\MoloniApi;

abstract class AbstractHookAction
{
    protected function isAuthenticated(): bool
    {
        return MoloniApi::hasValidAuthentication();
    }
}
