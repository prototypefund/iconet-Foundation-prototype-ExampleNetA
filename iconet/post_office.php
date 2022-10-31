<?php
$sim_post = true;
include_once './iconet/index.php';
class post_office
{

    function send($url, $message): string
    {
        $simulated = true;
        if (!$simulated){
            $url = 'https://'.$url.'/iconet/';
            $response = http_post_data($url, $message);
            return $response;
        } else {
            //simulate delivery inline
            echo "<br> Sending msg: <br>".$message . "<br>";

            $response = receive($message);

            echo "<br> Received msg: <br>".$response . "<br>";

            return $response;

        }
    }
}