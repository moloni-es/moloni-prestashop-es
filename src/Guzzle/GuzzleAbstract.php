<?php

namespace Moloni\Guzzle;

use GuzzleHttp\Client;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class GuzzleAbstract
{
    /**
     * @var Client|null
     */
    protected $client;

    /**
     * Plugin identifier
     *
     * @var string
     */
    protected $userAgent = 'PrestashopPlugin/2.0';
}