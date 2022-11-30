<?php

namespace Iconet;

class EmbeddedExperience
{
    public readonly string $format;
    public readonly string $content;

    public function __construct(string $format, string $content)
    {
        $this->format = $format;
        $this->content = $content;
    }

    public function render(): string
    {
        $token = bin2hex(openssl_random_pseudo_bytes(16));

        return
            "<embedded-experience
                format='$this->format'
                content='$this->content'
                token='$token'    
            ></embedded-experience>";
    }
}