<?php
    //string is a valid address, if part behind the last @ symbol is a valid url.
    function check_address($address){
        $string_array = explode("@",$address);
        if (count($string_array) < 2) return false;
        $url = $string_array[count($string_array)-1];
        if(filter_var($url, FILTER_VALIDATE_DOMAIN)){
            return true;
        } else {
            return false;
        }
    }


    function get_url($address){
        if (!check_address($address)) return false;
        $string_array = explode("@",$address);
        $url = $string_array[count($string_array)-1];
        return $url;
    }

?>