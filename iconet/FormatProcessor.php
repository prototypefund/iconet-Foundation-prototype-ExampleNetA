<?php

namespace Iconet;

use Cassandra\Collection;

class FormatProcessor
{
    protected string $separator_a;
    protected string $separator_b;

    public function __construct(){
        $this->separator_a = "['";
        $this->separator_b = "']";
    }


    function mergeFormat(string $format, array|string $content) : string
    {

        $separator_a = $this->separator_a;
        $separator_b = $this->separator_b;

        //find first usage of separator_a
        $substring_start= strpos($format, $separator_a);

        if(!$substring_start) return $format; //formating done if no more separator

        //substring starts, after separator
        $substring_start += strlen($separator_a);

        //stringlength of substring
        $size = strpos($format, $separator_b, $substring_start) - $substring_start;
        //extract substring
        $substring = substr($format, $substring_start, $size);
        //check if substring is a field in content
        if(!isset($content[$substring])){
            $error =  "Error: Missing field in Content Package: ". $substring . "<br>";
            echo $error;
            return $error;
        }

        // separators + substring are to be replace
        $replace = $separator_a . $substring . $separator_b;
        // replace format placeholder with given content
        $inserted = str_replace($replace, $content[$substring], $format);

        //replaced first occurence. replace entire format recursivly.
        $inserted = $this->mergeFormat($inserted, $content);

        return $inserted;
    }
}