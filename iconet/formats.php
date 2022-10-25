<?php
    //string is a valid address, if part behind the last @ symbol is a valid url.

    //receives an incoming iconet package, checks if all conditions are met
    function check_package($package){
        if (!isset($package['type'])) {
            echo "Error - Must set field 'type' <br>";
            return "Error - Must set field 'type' ";
        }


        switch ($package['type']){
            case "Request PublicKey":
                //check if non optional variables are set.
                if (isset($package['address'])){

                    //check if non optional variables are proper
                    if (check_address($package['address'])){
                        //all conditions for PublicKey Request are met.
                        return "Request PublicKey";
                    } else {
                        echo "Error - Faulty Address in PublicKey Request <br>" . $package['address'] . "<br>";
                        return "Error - Faulty Address in PublicKey Request";
                    }
                } else {
                    echo "Error - Missing Address in PublicKey Request <br>";
                    return "Error - Missing Address in PublicKey Request";
                }
                break;


            case "Send Notification":
                //check if non optional variables are set.
                if (isset($package["sender"]) and isset($package["notification"])){
                    //check if non optional variables are proper (can't check notification content, potentially encrypted)
                    if (check_address($package["sender"])) {
                        //all conditions for type Send Notification are met.
                        return "Send Notification";
                    }else{
                        echo "Error - Faulty sender address <br>";
                        return "Error - Faulty sender address";
                    }
                }else{
                    echo "Error - Missing field in Send Notification ('sender'/'notification') <br>";
                    return "Error - Missing field in Send Notification ('sender'/'notification')";
                }
                break;

            case "Request Format":
                //check if non optional variables are set.
                if (isset($package["format"])){
                    //check if non optional variables are proper (can't check notification content, potentially encrypted)
                    if ($package["format"] == "post-comments") {
                        //all conditions for type Send Notification are met.
                        return "Request Format";
                    }else{
                        echo "Error - Faulty Format, can only provide 'post-comments' <br>";
                        return "Error - Faulty Format, can only provide 'post-comments'";
                    }
                }else{
                    echo "Error - Missing field 'format' in Request Format <br>";
                    return "Error - Missing field 'format' in Request Format ";
                }
                break;

            case "Send Interaction":
                //check if non optional variables are set.
                if (isset($package["ID"]) and isset($package["sender"]) and isset($package["interaction"])){
                    //check if non optional variables are proper (can't check notification content, potentially encrypted)
                    if (check_address($package['sender'])) {
                        //all conditions for type Send Notification are met.
                        return "Send Interaction";
                    }else{
                        echo "Error - Faulty sender address' <br>";
                        return "Error - Faulty sender address'";
                    }
                }else{
                    echo "Error - Missing non optional field in Send Interaction (ID/sender/interaction)<br>";
                    return "Error - Missing non optional field in Send inteaction (ID/sender/interaction)";
                }
                break;


            default:
                echo "Unknown Package Type: <br>" . $package['type'] . "<br>";
                return "Error - Unknwon Package Type " . $package['type'];

        }
        echo "This section should never be reached. <br>";
        return false;
    }

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