<?php
error_reporting(E_ALL);
$config = new PrestaShop\CodingStandards\CsFixer\Config();

$config
    ->setUsingCache(false)
    ->getFinder()
    ->in(__DIR__);

return $config;
