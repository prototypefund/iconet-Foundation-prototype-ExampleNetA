<?php

namespace iconet;

require_once "../../config/config.php";
require_once "../Database.php";

if(isset($_GET['address'])) {
    $address = $_GET['address'];
    $db = new Database();
    $pk = $db->getPublickeyByAddress($address);
    $json = json_encode(array('address' => $address, 'publickey' => $pk));
    echo $json;
} else {
    echo "Here you may request public keys of our users. Ask via GET 'address=USER_ADDRESS'.";
}