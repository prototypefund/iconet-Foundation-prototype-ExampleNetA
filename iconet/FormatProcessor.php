<?php

namespace Iconet;


class FormatProcessor
{
    const separatorA = "\['"; //  [ needs escaping in regex
    const separatorB = "'\]";
    const regex = "|(" . self::separatorA . ")(\w+)(" . self::separatorB . ")|";

    protected array $content;

    /**
     * @param string $format
     * @param array<string> $content
     * @return string|null
     */
    function mergeFormat(string $format, array $content): string|null
    {
        $this->content = $content;

        $inserted = preg_replace_callback(
            self::regex,
            function($matches) {
                $content = $this->content;

                if(!isset($content[$matches[2]])) {
                    $error = "Error: Missing field in Content Package: " . $matches[2] . "<br>";
                    return $error;
                }
                return $content[$matches[2]];
            },
            $format);

        return $inserted;
    }




}