<?php

namespace Moloni\Exceptions\Product;

use Exception;

class MoloniProductException extends Exception
{
    protected $data;
    protected $identifiers;

    public function __construct($message, $identifiers = [], $data = [])
    {
        $this->data = $data;
        $this->identifiers = $identifiers;

        parent::__construct($message);
    }

    public function getData(): array
    {
        return $this->data ?? [];
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers ?? [];
    }
}