<?php

namespace Iconet;


class FormatProcessor
{

    protected string $separator_a;
    protected string $separator_b;
    protected array $content;


    public function __construct(){
        $this->separator_a = "\['"; // [ needs escaping in regex
        $this->separator_b = "'\]";
    }


    /**
     * @param string $format
     * @param array<string> $content
     * @return string|null
     */
    function mergeFormat(string $format, array $content) : string|null
    {
        $this->content = $content;
        $regex = "|(". $this->separator_a. ")([a-z]*)(".$this->separator_b.")|";

        $inserted = preg_replace_callback($regex,
            function ($matches)
            {
                $content = $this->content;

                if(!isset($content[$matches[2]])){
                    $error =  "Error: Missing field in Content Package: ". $matches[2] . "<br>";
                     return $error;
                }
                return $content[$matches[2]];
            },
            $format);

        return $inserted;
    }




}