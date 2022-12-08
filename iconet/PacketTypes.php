<?php

namespace Iconet;

enum PacketTypes: string
{
    case ERROR = "Error";
    case ACK = "ACK";
    case PUBLIC_KEY_REQUEST = "PublicKeyRequest";
    case PUBLIC_KEY_RESPONSE = "PublicKeyResponse";
    case NOTIFICATION = "Notification";
    case CONTENT_REQUEST = "ContentRequest";
    case CONTENT_RESPONSE = "ContentResponse";
    case FORMAT_REQUEST = "FormatRequest";
    case FORMAT_RESPONSE = "FormatResponse";
    case INTERACTION = "Interaction";
}