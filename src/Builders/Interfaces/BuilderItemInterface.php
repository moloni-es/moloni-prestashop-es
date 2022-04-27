<?php

namespace Moloni\Builders\Interfaces;

interface BuilderItemInterface
{
    public function toArray();
    public function insert();
    public function search();
}
