<?php

namespace Iconet;

class Contact
{
    public readonly Address $address;
    public readonly string $publicKey;

    public function __construct(Address $address, string $publicKey)
    {
        $this->address = $address;
        $this->publicKey = $publicKey;
    }
}