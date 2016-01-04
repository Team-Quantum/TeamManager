<?php
/**
 * Created by PhpStorm.
 * User: .PolluX
 * Date: 1/4/2016
 * Time: 1:01 AM
 */

namespace TeamManager\Crypt;


class Crypting
{
    /**
     * @var string
     */
    public $encryption_key;

    /**
     * @return string
     */
    public function getEncryptionKey(){
        return $this->encryption_key;
    }

    /**
     * @param string $encryption_key
     */
    public function setEncryptionKey($encryption_key){
        $this->encryption_key = $encryption_key;
    }

    /**
     * @param $clearstring
     * @return string
     */
    public function encrypt($clearstring) {
        $encryptionKey = $this->getEncryptionKey();

        $size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        $a = mcrypt_create_iv($size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryptionKey, utf8_encode($clearstring), MCRYPT_MODE_CBC, $a);

        return $encrypted_string;
    }

    /**
     * @param $encrypted_string
     * @return string
     */
    public function decrypt($encrypted_string) {
        $encryptionKey = $this->getEncryptionKey();

        $size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        $a = mcrypt_create_iv($size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryptionKey, $encrypted_string, MCRYPT_MODE_CBC, $a);

        return $decrypted_string;
    }
}