<?php

namespace Iconet;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;


class S2STransmitter
{

    /**
     * @throws RuntimeException When there is no valid http response.
     */
    public function send(Address $address, string $message): string
    {
        $url = $address->getEndpoint();
        $query = [];

        if($address->isInternal || $_ENV['DEBUG_SIM_REQ']) {
            //TODO The data flow for packets that are handled by the own server can be branched off earlier.
            $result = (new S2SReceiver())->receive($message);
        } else {
            if($_ENV['DEBUG_NO_EXT_REQ']) {
                // redirect all outgoing requests to this server
                $url = Address::fromUsername('local')->getEndpoint();
                $query = ["XDEBUG_SESSION_START" => $_ENV['XDEBUG_SESSION_START']];
            }

            $client = new Client(['timeout' => 2.0,]);

            try {
                $response = $client->post($url, ['body' => $message, 'query' => $query]);
            } catch(GuzzleException $ex) {
                throw new RuntimeException(
                    "Got no valid http response from url '$url'",
                    $ex->getCode(), $ex
                );
            }

            $code = $response->getStatusCode();

            if($code != 200) {
                throw new RuntimeException("Got https response $code from $url");
            }

            $result = (string)$response->getBody();
        }
        return $result;
    }
}