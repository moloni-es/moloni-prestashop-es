<?php

namespace Moloni\Builders\Interfaces;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface BuilderItemInterface
{
    public function toArray();
    public function insert();
    public function search();
}
