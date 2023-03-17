<?php

namespace Moloni\Guzzle;

use GuzzleHttp\Client;

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