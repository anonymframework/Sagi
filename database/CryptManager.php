<?php

namespace Sagi\Database;


class CryptManager
{

    /**
     * @param $value
     * @return string
     */
    public static function encode($value)
    {
        return mcrypt_encrypt(MCRYPT_3DES, static::prepareKey(ConfigManager::get('PRIVATE_KEY')), $value, MCRYPT_MODE_ECB);
    }

    private static function prepareKey($key){
        return substr($key, 0, 24);
    }

    /**
     * @param $value
     * @return string
     */
    public static function decode($value)
    {
        return mcrypt_decrypt(MCRYPT_3DES, static::prepareKey( ConfigManager::get('PRIVATE_KEY')), $value, MCRYPT_MODE_ECB);
    }
}