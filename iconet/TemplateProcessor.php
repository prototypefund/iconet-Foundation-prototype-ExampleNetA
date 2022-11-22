<?php

namespace Iconet;


class TemplateProcessor
{
    private const REGEX = "/\['(\w+)'\]/";


    /**
     * @param string $template
     * @param array<string, string> $contentMap Maps placeholder names onto content strings.
     * @return string|null The format witch filled in content or null on failure.
     */
    public static function fillTemplate(string $template, array $contentMap): string|null
    {
        return preg_replace_callback(
            self::REGEX,
            function($matches) use ($contentMap) {
                $placeholder = $matches[1];
                if(!isset($contentMap[$placeholder])) {
                    $error = "Error: Missing field in Content Package: $placeholder <br>";
                    // TODO handle errors
                    return $error;
                }
                return $contentMap[$placeholder];
            },
            $template
        );
    }




}