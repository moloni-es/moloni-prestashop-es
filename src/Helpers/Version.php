<?php

namespace Moloni\Helpers;

class Version
{
    public static function isPrestashopNewVersion(): bool
    {
        return version_compare(_PS_VERSION_, '8') >= 0;
    }
}