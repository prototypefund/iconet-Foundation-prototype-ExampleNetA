<?php

namespace Iconet;

enum PacketTypes: string
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