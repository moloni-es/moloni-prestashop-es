<?php

namespace Moloni\Enums;

class StockSync
{
    public const IGNORED_REFERENCES = [
        'envio',
        'envío'
    ];

    public static function isIgnoredReference(string $reference): bool
    {
        return in_array($reference, self::IGNORED_REFERENCES);
    }
}
