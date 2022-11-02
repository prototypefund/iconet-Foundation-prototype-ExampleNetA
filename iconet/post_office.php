<?php
namespace iconet;
$sim_post = true;
include_once './iconet/index.php';
class post_office
{
    //todo
    function send($url, $message): string
    {
        $simulated = true;
        if (!$simulated){
            //todo
            $url = 'https://'.$url.'/iconet/';
            $response = http_post_data($url, $message);
            return $response;
        } else {
            //simulate delivery inline
            echo "<br> Sending msg: <br>".htmlspecialchars($message) . "<br>";

            $response = receive($message);

            echo "<br> Received msg: <br>".htmlspecialchars($response) . "<br>";

            return $response;

        }
    }
}