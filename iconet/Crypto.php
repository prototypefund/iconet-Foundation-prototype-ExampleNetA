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
        $privateKey = openssl_pkey_new($this->configs);
        $publicKey_pem = openssl_pkey_get_details($privateKey)['key'];
        $publicKey = openssl_pkey_get_public($publicKey_pem);
        openssl_pkey_export($privateKey,$privateKey_pem, null, $this->configs);
        return [$publicKey_pem,$privateKey_pem];
    }

    function verSignature($content,$privateKey): string
        //TODO test function
    {
        openssl_sign($content, $signature, $privateKey, OPENSSL_ALGO_SHA1);
        return $signature;
    }

    function genSymKey(): string
    {
        $size = 128;
        $secret = openssl_random_pseudo_bytes($size);
        $secret = base64_encode($secret);
        return $secret;
    }

    /**
     * @throws Exception
     */
    function encSym($data,$secret): string
    {
        $AES = new AES($data,$secret,$this->blockSize);
        $encryptedData = $AES ->encrypt();
        return base64_encode($encryptedData);
    }

    /**
     * @throws Exception
     */
    function decSym($encryptedData,$secret):string
    {
        $encryptedData = base64_decode($encryptedData);
        $AES = new AES($encryptedData,$secret,$this->blockSize);
        return $AES ->decrypt();
    }

    function genAllCiphers($contacts,$secret): array
    {
        $i=0;
        $encryptedSecrets = array();
        foreach ($contacts as $c){
            $encryptedSecret['address'] = $c['address'];
            $encryptedSecret['encryptedSecret'] = $this->encAsym($secret, $c['pubkey'] );
            $encryptedSecrets[$i] = $encryptedSecret;
            $i++;
        }
        return $encryptedSecrets;
    }

    function encAsym($secret,$publicKey): string
    {
        openssl_public_encrypt($secret, $encryptedSecret, $publicKey, OPENSSL_PKCS1_PADDING);
        $encryptedSecret = base64_encode($encryptedSecret);
        return $encryptedSecret;
    }

    function decAsym($encryptedSecret,$privateKey): string
    {
        $encryptedSecret = base64_decode($encryptedSecret);
        openssl_private_decrypt($encryptedSecret, $secret, $privateKey, OPENSSL_PKCS1_PADDING);
        return $secret;
    }

}