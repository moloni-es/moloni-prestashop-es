<?php

namespace Moloni\Enums;

class SyncFields
{
    public const NAME = 'name';
    public const PRICE = 'price';
    public const DESCRIPTION = 'description';
    public const CATEGORIES = 'categories';
    public const IMAGE = 'image';

    public static function getSyncFields(): array
    {
        return [
            'Name' => self::NAME,
            'Price' => self::PRICE,
            'Description' => self::DESCRIPTION,
            'Categories' => self::CATEGORIES,
            'Image' => self::IMAGE,
        ];
    }

    public static function getDefaultFields(): array
    {
        return [
            self::NAME,
            self::PRICE,
            self::CATEGORIES,
            self::DESCRIPTION,
        ];
    }
}
