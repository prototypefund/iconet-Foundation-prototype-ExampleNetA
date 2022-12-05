<?php

use Iconet\Crypto;
use PHPUnit\Framework\TestCase;

class CryptoTest extends TestCase
{

    private Crypto $crypto;

    protected function setUp(): void
    {
        $this->crypto = new Crypto();
    }

    public function testSymCrypto(): void
    {
        $secret = $this->crypto->genSymKey();
        $content = "Hello Bob, here are some funny symbols: äöü123{}ß. Love you!";

        $encryptedContent = $this->crypto->encSym($content, $secret);
        $failedEncryption1 = $this->crypto->encSym("wrongContent", $secret);

        $decryptedContent = $this->crypto->decSym($encryptedContent, $secret);
        $failedDecryption1 = $this->crypto->decSym($failedEncryption1, $secret);
        $failedDecryption2 = $this->crypto->decSym($encryptedContent, "wrongSecret");
        $failedDecryption3 = $this->crypto->decSym("wrongEncryptedContent", $secret);


        self::assertIsString($secret);

        self::assertIsString($encryptedContent);
        self::assertIsString($failedEncryption1);

        self::assertEquals($content, $decryptedContent);
        self::assertNotEquals($content, $failedDecryption1);
        self::assertNotEquals($content, $failedDecryption2);
        self::assertNotEquals($content, $failedDecryption3);
    }

    public function testAsymCrypto(): void
    {
        $keyPair = $this->crypto->genKeyPair();
        $invalidKeyPair = false;
        $data = "ThisIsTheSecret!äöü123{}ß";

        $encryptedData = $this->crypto->encAsym($data, $keyPair[0]);
        $failedEncryption1 = $this->crypto->encSym($data, $invalidKeyPair);
        $failedEncryption2 = $this->crypto->encSym("wrongData", $keyPair[0]);

        $decryptedData = $this->crypto->decAsym($encryptedData, $keyPair[1]);
        $failedDecryption1 = $this->crypto->decSym($encryptedData, $invalidKeyPair);
        $failedDecryption2 = $this->crypto->decSym($failedEncryption2, $keyPair[1]);
        $failedDecryption3 = $this->crypto->decSym($encryptedData, "wrongKeyPair");
        $failedDecryption4 = $this->crypto->decSym("wrongEncryptedData", $keyPair[1]);


        self::assertIsArray($keyPair);
        self::assertIsString($keyPair[0]);
        self::assertIsString($keyPair[1]);
        self::assertIsNotArray($invalidKeyPair);

        self::assertIsString($encryptedData);
        self::assertIsString($failedEncryption1);
        self::assertIsString($failedEncryption2);

        self::assertEquals($data, $decryptedData);
        self::assertNotEquals($data, $failedDecryption1);
        self::assertNotEquals($data, $failedDecryption2);
        self::assertNotEquals($data, $failedDecryption3);
        self::assertNotEquals($data, $failedDecryption4);
    }

}
