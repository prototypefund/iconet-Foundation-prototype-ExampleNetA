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
        return
            "<embedded-experience
                format='$this->format'
                content='$this->content'
            ></embedded-experience>";
    }
}