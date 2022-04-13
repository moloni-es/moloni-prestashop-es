<?php
error_reporting(E_ALL);
$config = new PrestaShop\CodingStandards\CsFixer\Config();

$config
    ->setUsingCache(true)
    ->getFinder()
    ->exclude('vendor')
    ->in(__DIR__);

return $config;
