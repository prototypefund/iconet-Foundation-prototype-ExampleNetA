<?php
$pubkey = "";
// provides public key from $address by requesting it from external server
if (isset($address)){
    //TODO Server2Server request. Temporary trivial placeholder.
    $pubkey = $address . "123PK";
}else {
    echo "Error in Pubkey Request: No address was set.";
}
?>
