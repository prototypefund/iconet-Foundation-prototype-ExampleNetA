<?php

namespace Iconet;

class EmbeddedExperience
{
    private string $contentData;

    public function __construct(mixed $contentData)
    {
        $this->contentData = htmlentities(json_encode($contentData));
    }

    public function render(): string
    {
        return
            "<embedded-experience
                contentData='$this->contentData'
            ></embedded-experience>";
    }
}