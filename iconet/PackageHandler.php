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

            case "PublicKey Request":
                //check if non-optional variables are set.
                if (isset($package['address'])){
                    //check if non-optional variables are proper
                    if ($this->check_address($package['address'])){
                        //all conditions for publicKey request are met.
                        return "PublicKey Request";
                    } else {
                        echo "Error - Faulty address in publicKey request <br>" . $package['address'] . "<br>";
                        return "Error - Faulty address in publicKey request";
                    }
                } else {
                    echo "Error - Missing address in publicKey request <br>";
                    return "Error - Missing address in publicKey request";
                }
                break;

            case "PublicKey Response":
                //check if non-optional variables are set.
                if (isset($package['address'])and isset($package['publicKey'])){
                    //check if non-optional variables are proper
                    if ($this->check_address($package['address'])){
                        //all conditions for publicKey request are met.
                        return "PublicKey Response";
                    } else {
                        echo "Error - Faulty address in publicKey response <br>" . $package['address'] . "<br>";
                        return "Error - Faulty address in publicKey response";
                    }
                } else {
                    echo "Error - Missing field in publicKey response ('address'/'publicKey') <br>";
                    return "Error - Missing field in publicKey response ('address'/'publicKey')";
                }
                break;

            case "Notification":
                //check if non-optional variables are set.
                if (isset($package["actor"]) and isset($package["predata"]) and isset($package['encryptedSecret']) and isset($package['to'])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($this->check_address($package["actor"]) and $this->check_address($package['to'])) {
                        //all conditions for type send notification are met.
                        return "Notification";
                    } else{
                        echo "Error - Faulty actor or recipient address <br>";
                        return "Error - Faulty actor or recipient address";
                    }
                } else{
                    echo "Error - Missing field in notification ('actor'/'predata'/'ecryptedSecret'/'to') <br>";
                    return "Error - Missing field in notification ('actor'/'predata'/'ecryptedSecret'/'to')";
                }
                break;

            case "Content Request":
                //check if non-optional variables are set.
                if (isset($package["id"]) and isset($package["actor"])){
                    return "Content Request";
                } else{
                    echo "Error - Missing field in content request ('id'/'actor') <br>";
                    return "Error - Missing field in content request ('id'/'actor')";
                }
                break;

            case "Content Response":
                //check if non-optional variables are set.
                if (isset($package["actor"]) and $package["formatId"] and $package["content"]){
                    if ($this->check_address($package['actor'])) {
                        //all conditions for type send interaction are met.
                        return "Content Response";
                    } else{
                        echo "Error - Faulty actor address <br>";
                        return "Error - Faulty actor address";
                    }
                } else{
                    echo "Error - Missing field in content response ('actor'/'formatId'/'content')<br>";
                    return "Error - Missing field in content response ('actor'/'formatId'/'content')";
                }
                break;

            case "Format Request":
                //check if non-optional variables are set.
                if (isset($package["formatId"])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($package["formatId"] == "post-comments") {
                        //all conditions for type request format are met.
                        return "Format Request";
                    } else{
                        echo "Error - Faulty formatId, can only provide 'post-comments'<br>";
                        return "Error - Faulty formatId, can only provide 'post-comments'";
                    }
                } else{
                    echo "Error - Missing field 'formatId' in format request <br>";
                    return "Error - Missing field 'formatId' in format request";
                }
                break;

            case "Format Response":
                //check if non-optional variables are set.
                if (isset($package["formatId"]) and $package["format"]){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($package["formatId"] == "post-comments") {
                        //all conditions for type request format are met.
                        return "Format Response";
                    } else{
                        echo "Error - Faulty format formatId, can only provide 'post-comments' <br>";
                        return "Error - Faulty format formatId, can only provide 'post-comments'";
                    }
                } else{
                    echo "Error - Missing field in format response ('formatId'/'format')<br>";
                    return "Error - Missing field in format response ('formatId'/'format')";
                }
                break;

            case "Interaction":
                //check if non-optional variables are set.
                if (isset($package["id"]) and isset($package["actor"]) and isset($package["interaction"])){
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if ($this->check_address($package['actor'])) {
                        //all conditions for type send interaction are met.
                        return "Interaction";
                    } else{
                        echo "Error - Faulty actor address<br>";
                        return "Error - Faulty actor address";
                    }
                } else{
                    echo "Error - Missing field in interaction ('id'/'actor'/'interaction')<br>";
                    return "Error - Missing field in interaction ('id'/'actor'/'interaction')";
                }
                break;

            default:
                echo "Unknown package type: <br>" . $package['type'] . "<br>";
                return "Error - Unknown package type " . $package['type'];

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