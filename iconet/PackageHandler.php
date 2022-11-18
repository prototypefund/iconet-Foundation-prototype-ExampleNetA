<?php
namespace Iconet;


enum PackageTypes: string
{
    case INVALID = "";
    case PUBLICKEY_REQUEST = "PublicKey Request";
    case PUBLICKEY_RESPONSE = "PublicKey Response";
    case NOTIFICATION = "Notification";
    case CONTENT_REQUEST = "Content Request";
    case CONTENT_RESPONSE = "Content Response";
    case FORMAT_REQUEST = "Format Request";
    case FORMAT_RESPONSE = "Format Response";
    case INTERACTION = "Interaction";
}

class PackageHandler
{
    //TODO split this into getType and validation logic
    //TODO remove echos
    //TODO remove redundancy: Packages can be objects that have a property for required fields

    /**
     * @param mixed $package
     * @return PackageTypes The determined type of the package (includes INVALID)
     */
    public static function checkPackage(mixed $package): PackageTypes
    {
        if(!isset($package->type)) {
            echo "Error - Must set field 'type' <br>";
            return PackageTypes::INVALID;
        }

        $type = PackageTypes::tryFrom($package->type);
        if(!$type) {
            return PackageTypes::INVALID;
        }

        switch($type) {
            case PackageTypes::PUBLICKEY_REQUEST:
                //check if non-optional variables are set.
                if(isset($package->address)) {
                    //check if non-optional variables are proper
                    if(Address::validate($package->address)) {
                        //all conditions for publicKey request are met.
                        return PackageTypes::PUBLICKEY_REQUEST;
                    } else {
                        echo "Error - Faulty address in publicKey request <br>" . $package->address . "<br>";
                        return PackageTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing address in publicKey request <br>";
                    return PackageTypes::INVALID;
                }

            case PackageTypes::PUBLICKEY_RESPONSE:
                //check if non-optional variables are set.
                if(isset($package->address) && isset($package->publicKey)) {
                    //check if non-optional variables are proper
                    if(Address::validate($package->address)) {
                        //all conditions for publicKey request are met.
                        return PackageTypes::PUBLICKEY_RESPONSE;
                    } else {
                        echo "Error - Faulty address in publicKey response <br>" . $package->address . "<br>";
                        return PackageTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in publicKey response ('address'/'publicKey') <br>";
                    return PackageTypes::INVALID;
                }

            case PackageTypes::NOTIFICATION:
                //check if non-optional variables are set.
                if(isset($package->actor) && isset($package->predata) && isset($package->encryptedSecret) && isset($package->to)) {
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if(Address::validate($package->actor) && Address::validate($package->to)) {
                        //all conditions for type send notification are met.
                        return PackageTypes::NOTIFICATION;
                    } else {
                        echo "Error - Faulty actor or recipient address <br>";
                        return PackageTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in notification ('actor'/'predata'/'ecryptedSecret'/'to') <br>";
                    return PackageTypes::INVALID;
                }

            case PackageTypes::CONTENT_REQUEST:
                //check if non-optional variables are set.
                if(isset($package->id) && isset($package->actor)) {
                    return PackageTypes::CONTENT_REQUEST;
                } else {
                    echo "Error - Missing field in content request ('id'/'actor') <br>";
                    return PackageTypes::INVALID;
                }

            case PackageTypes::CONTENT_RESPONSE:
                //check if non-optional variables are set.
                if(isset($package->actor) && isset($package->formatId) && isset($package->content)) {
                    if(Address::validate($package->actor)) {
                        //all conditions for type send interaction are met.
                        return PackageTypes::CONTENT_RESPONSE;
                    } else {
                        echo "Error - Faulty actor address <br>";
                        return PackageTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in content response ('actor'/'formatId'/'content')<br>";
                    return PackageTypes::INVALID;
                }

            case PackageTypes::FORMAT_REQUEST:
                //check if non-optional variables are set.
                if(isset($package->formatId)) {
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if($package->formatId) {  //TODO specify syntax for format ids
                        //all conditions for type request format are met.
                        return PackageTypes::FORMAT_REQUEST;
                    } else {
                        echo "Error - Faulty formatId, can only provide 'post-comments'<br>";
                        return PackageTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field 'formatId' in format request <br>";
                    return PackageTypes::INVALID;
                }

            case PackageTypes::FORMAT_RESPONSE:
                //check if non-optional variables are set.
                if(isset($package->formatId) && isset($package->format)) {
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if($package->formatId) { //TODO specify syntax for format ids
                        //all conditions for type request format are met.
                        return PackageTypes::FORMAT_RESPONSE;
                    } else {
                        echo "Error - Faulty format formatId, can only provide 'post-comments' <br>";
                        return PackageTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in format response ('formatId'/'format')<br>";
                    return PackageTypes::INVALID;
                }

            case PackageTypes::INTERACTION:
                //check if non-optional variables are set.
                if(isset($package->id) && isset($package->actor) && isset($package->interaction)) {
                    //check if non-optional variables are proper (can't check notification content, potentially encrypted)
                    if(Address::validate($package->actor)) {
                        //all conditions for type send interaction are met.
                        return PackageTypes::INTERACTION;
                    } else {
                        echo "Error - Faulty actor address<br>";
                        return PackageTypes::INVALID;
                    }
                } else {
                    echo "Error - Missing field in interaction ('id'/'actor'/'interaction')<br>";
                    return PackageTypes::INVALID;
                }

            default:
                echo "Unknown package type: <br>" . $package->type . "<br>";
                return PackageTypes::INVALID;
        }

    }

}