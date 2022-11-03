<?php
namespace Iconet;


//TODO use objects or make all methods static
class PackageHandler
{
    function check_package($package): string
    {

        if (!isset($package['type'])) {
            echo "Error - Must set field 'type' <br>";
            return "Error - Must set field 'type' ";
        }

        switch ($package['type']){
            case "Request Publickey":
                //check if non-optional variables are set.
                if (isset($package['address'])){
                    //check if non-optional variables are proper
                    if ($this->check_address($package['address'])){
                        //all conditions for publicKey request are met.
                        return "Request Publickey";
                    } else {
                        echo "Error - Faulty address in publicKey request <br>" . $package['address'] . "<br>";
                        return "Error - Faulty address in publicKey request";
                    }
                } else {
                    echo "Error - Missing address in publicKey request <br>";
                    return "Error - Missing address in publicKey request";
                }
                break;

            case "Send Notification":
                //check if non-optional variables are set.
                if (isset($package["sender"]) and isset($package["predata"]) and isset($package['cipher']) and isset($package['to'])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($this->check_address($package["sender"]) and $this->check_address($package['to'])) {
                        //all conditions for type send notification are met.
                        return "Send Notification";
                    } else{
                        echo "Error - Faulty sender address <br>";
                        return "Error - Faulty sender address";
                    }
                } else{
                    echo "Error - Missing field in send notification ('sender'/'notification') <br>";
                    return "Error - Missing field in send notification ('sender'/'notification')";
                }
                break;

            case "Request Format":
                //check if non-optional variables are set.
                if (isset($package["name"])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($package["name"] == "post-comments") {
                        //all conditions for type request format are met.
                        return "Request Format";
                    } else{
                        echo "Error - Faulty format, can only provide 'post-comments' <br>";
                        return "Error - Faulty format, can only provide 'post-comments'";
                    }
                } else{
                    echo "Error - Missing field 'format' in request format <br>";
                    return "Error - Missing field 'format' in request format ";
                }
                break;

            case "Send Interaction":
                //check if non-optional variables are set.
                if (isset($package["id"]) and isset($package["sender"]) and isset($package["interaction"])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($this->check_address($package['sender'])) {
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

            case "Request Content":
                //check if non-optional variables are set.
                if (isset($package["id"]) and isset($package["address"])){
                    return "Request Content";
                } else{
                    echo "Error - Missing field 'ID', 'address' in request content <br>";
                    return "Error - Missing field 'ID', 'address' in request content ";
                }
                break;

            case "Send Content":
                //check if non-optional variables are set.
                if (isset($package["sender"]) and $package["format"] and $package["content"]){
                    if ($this->check_address($package['sender'])) {
                        //all conditions for type send interaction are met.
                        return "Send Content";
                    } else{
                        echo "Error - Faulty sender address' <br>";
                        return "Error - Faulty sender address'";
                    }
                } else{
                    echo "Error - Missing field 'sender', 'format' or 'content' in send content <br>";
                    return "Error - Missing field 'sender', 'format' or 'content' in send content ";
                }
                break;
            case "Send Format":
                //check if non-optional variables are set.
                if (isset($package["name"]) and $package["format"]){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($package["name"] == "post-comments") {
                        //all conditions for type request format are met.
                        return "Send Format";
                    } else{
                        echo "Error - Faulty format name, can only provide 'post-comments' <br>";
                        return "Error - Faulty format name, can only provide 'post-comments'";
                    }
                } else{
                    echo "Error - Missing field 'name' or 'format' in send format <br>";
                    return "Error - Missing field 'name' or 'format'  in send format ";
                }
                break;
            default:
                echo "Unknown Package Type: <br>" . $package['type'] . "<br>";
                return "Error - Unknwon Package Type " . $package['type'];

        }


    }

    public static function check_address($address): bool
    {
        $string_array = explode("@",$address);
        if (count($string_array) < 2) return false;
        $url = $string_array[count($string_array)-1];
        if(filter_var($url, FILTER_VALIDATE_DOMAIN)){
            return true;
        } else {
            return false;
        }
    }


}