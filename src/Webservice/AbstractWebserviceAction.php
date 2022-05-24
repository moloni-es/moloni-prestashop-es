<?php

namespace Moloni\Webservice;

abstract class AbstractWebserviceAction
{
    protected $isAuthenticated = false;

    public function __construct()
    {
        $this->isAuthenticated = true;
    }
}
