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
            $pretty = json_encode(json_decode($message), JSON_PRETTY_PRINT);
            echo "<br> Sending msg: <pre>".$pretty . "</pre>>";

            $response = receive($message);

            $pretty = json_encode(json_decode($response), JSON_PRETTY_PRINT);

            echo "<br> Received msg: <pre>".htmlspecialchars($pretty) . "</pre>";

            return $response;

        }
    }
}