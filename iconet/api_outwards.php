<?php
// this file handles messages outside.
include_once 'index.php'; // for simulating s2s, we'll use the receive function of index

function send($url, $message){
    //for now, s2s communication only simulated.
    $response = receive($message); //function of api-inwards from index.
    return $response;
}
?>