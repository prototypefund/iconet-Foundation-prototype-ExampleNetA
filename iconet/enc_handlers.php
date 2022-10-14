<?php
genKeyPair():keypair //['public','private']

genSymKey():$symKey

encSym($data,$symKey):$encrypted

decSym($encrypted,$symKey):$data

genAllTokens($userLoggedIn,$symKey):tokenlist //array($address,$token)

encAsym($data,$pubKey):$encrypted

decAsym($encrypted,$privKey):$data

openToken($token):$symKey

?>