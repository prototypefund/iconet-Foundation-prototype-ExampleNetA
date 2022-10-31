<?php
include_once './iconet/libs/AES.php';
include_once './iconet/database.php';

class cryptograph
{

    protected int $blockSize;
    protected $configs;
    protected $db;

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
        $this->db = new database();

    }

    function genKeyPair(): array
    {
        $privkey = openssl_pkey_new($this->configs);
        $pubKey_pem = openssl_pkey_get_details($privkey)['key'];
        $pubKey = openssl_pkey_get_public($pubKey_pem);
        openssl_pkey_export($privkey,$privkey_pem, null, $this->configs);
        return [$pubKey_pem,$privkey_pem];
    }

    function verSignature($message,$privKey){
        openssl_sign($message, $signature, $privKey, OPENSSL_ALGO_SHA1);
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

    function encAsym($symKey,$pubKey)
    {
        openssl_public_encrypt($symKey, $encSymKey, $pubKey, OPENSSL_PKCS1_PADDING);
        $encSymKey = base64_encode($encSymKey);
        return $encSymKey;
    }

    function decAsym($cipher,$privKey)
    {
        $cipher = base64_decode($cipher);
        openssl_private_decrypt($cipher, $decSecret, $privKey, OPENSSL_PKCS1_PADDING);
        return $decSecret;
    }



}