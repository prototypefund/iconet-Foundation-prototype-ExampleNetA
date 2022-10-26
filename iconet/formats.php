<?php
    //string is a valid address, if part behind the last @ symbol is a valid url.

    //receives an incoming iconet package, checks if all conditions are met
    function check_package($package){
        if (!isset($package['type'])) {
            echo "Error - Must set field 'type' <br>";
            return "Error - Must set field 'type' ";
        }

        switch ($package['type']){
            case "Request publicKey":
                //check if non-optional variables are set.
                if (isset($package['address'])){
                    //check if non-optional variables are proper
                    if (check_address($package['address'])){
                        //all conditions for publicKey request are met.
                        return "Request publicKey";
                    } else {
                        echo "Error - Faulty address in publicKey request <br>" . $package['address'] . "<br>";
                        return "Error - Faulty address in publicKey request";
                    }
                } else {
                    echo "Error - Missing address in publicKey request <br>";
                    return "Error - Missing address in publicKey request";
                }
                break;

            case "Send notification":
                //check if non-optional variables are set.
                if (isset($package["sender"]) and isset($package["predata"]) and isset($package['cipher'])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if (check_address($package["sender"])) {
                        //all conditions for type send notification are met.
                        return "Send notification";
                    } else{
                        echo "Error - Faulty sender address <br>";
                        return "Error - Faulty sender address";
                    }
                } else{
                    echo "Error - Missing field in send notification ('sender'/'notification') <br>";
                    return "Error - Missing field in send notification ('sender'/'notification')";
                }
                break;

            case "Request format":
                //check if non-optional variables are set.
                if (isset($package["format"])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($package["format"] == "post-comments") {
                        //all conditions for type request format are met.
                        return "Request format";
                    } else{
                        echo "Error - Faulty format, can only provide 'post-comments' <br>";
                        return "Error - Faulty format, can only provide 'post-comments'";
                    }
                } else{
                    echo "Error - Missing field 'format' in request format <br>";
                    return "Error - Missing field 'format' in request format ";
                }
                break;

            case "Send interaction":
                //check if non-optional variables are set.
                if (isset($package["ID"]) and isset($package["sender"]) and isset($package["interaction"])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if (check_address($package['sender'])) {
                        //all conditions for type send interaction are met.
                        return "Send Interaction";
                    } else{
                        echo "Error - Faulty sender address' <br>";
                        return "Error - Faulty sender address'";
                    }
                } else{
                    echo "Error - Missing non-optional field in send interaction (ID/sender/interaction)<br>";
                    return "Error - Missing non-optional field in send interaction (ID/sender/interaction)";
                }
                break;

            case "Request content":
                //check if non-optional variables are set.
                if (isset($package["ID"])){
                    return "Request content";
                } else{
                    echo "Error - Missing field 'ID' in request content <br>";
                    return "Error - Missing field 'ID' in request content ";
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