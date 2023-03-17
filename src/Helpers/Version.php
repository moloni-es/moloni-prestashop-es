<?php

namespace Moloni\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Version
{
    public static function isPrestashopNewVersion(): bool
    {
        return version_compare(_PS_VERSION_, '8') >= 0;
    }
}