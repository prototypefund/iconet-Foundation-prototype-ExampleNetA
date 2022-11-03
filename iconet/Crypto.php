<?php
namespace Iconet;
include_once './iconet/libs/AES.php';


class Crypto
{

    protected int $blockSize;
    protected $configs;

    public function __construct()
    {
        $this->blockSize = 256;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->configs = array(
                //windows must import openssl configs
                "config" => "C:/xampp/php/extras/openssl/openssl.cnf",
                'private_key_bits'=> 2048,
                'default_md' => "sha256",
            );
        } else {
            //linux must not import openssl configs
            $this->configs = null;
        }

    }

    function genKeyPair(): array
    {
        $privkey = openssl_pkey_new($this->configs);
        $pubKey_pem = openssl_pkey_get_details($privkey)['key'];
        $pubKey = openssl_pkey_get_public($pubKey_pem);
        openssl_pkey_export($privkey,$privkey_pem, null, $this->configs);
        return [$pubKey_pem,$privkey_pem];
    }

    function verSignature($message,$privkey)
    {
        openssl_sign($message, $signature, $privkey, OPENSSL_ALGO_SHA1);
        return $signature;
    }

    function genSymKey(): string
    {
        $size = 128;
        $key = openssl_random_pseudo_bytes($size);
        $key = base64_encode($key);
        return $key;
    }

    /**
     * @throws Exception
     */
    function encSym($data, $key)
    {
        $AES = new AES($data, $key,$this->blockSize);
        $encrypted = $AES ->encrypt();
        return base64_encode($encrypted);
    }

    /**
     * @throws Exception
     */
    function decSym($encrypted, $key)
    {
        $encrypted = base64_decode($encrypted);
        $AES = new AES($encrypted, $key,$this->blockSize);
        return $AES ->decrypt();
    }

    function genAllCiphers($contacts,$secret): array
    {
        $i=0;
        $ciphers = array();
        foreach ($contacts as $c){
            $cipher['address'] = $c['address'];
            $cipher['cipher'] = $this->encAsym($secret, $c['pubkey'] );
            $ciphers[$i] = $cipher;
            $i++;
        }
        return $ciphers;
    }

    function encAsym($data,$pubKey)
    {
        openssl_public_encrypt($data, $encdata, $pubKey, OPENSSL_PKCS1_PADDING);
        $encdata = base64_encode($encdata);
        return $encdata;
    }

    function decAsym($encdata,$privKey)
    {
        $encdata = base64_decode($encdata);
        openssl_private_decrypt($encdata, $data, $privKey, OPENSSL_PKCS1_PADDING);
        return $data;
    }



}