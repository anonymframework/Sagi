<?php

namespace Sagi\Database;


class CryptManager
{

    /**
     * @var string
     */
    private static $key;

    /**
     * @return string
     */
    public static function getKey()
    {
        if(empty(static::$key)){
            static::$key = ConfigManager::get('PRIVATE_KEY');
        }

        return self::$key;
    }

    /**
     * @param string $key
     */
    public static function setKey($key)
    {
        self::$key = $key;
    }

    /**
     * @param $value
     * @return string
     */
    public static function encode($value)
    {
        return mcrypt_encrypt(MCRYPT_3DES, static::prepareKey(static::getKey()), $value, MCRYPT_MODE_ECB);
    }

    /**
     * @param $key
     * @return string
     */
    private static function prepareKey($key){
        return substr($key, 0, 24);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function decode($value)
    {
        return mcrypt_decrypt(MCRYPT_3DES, static::prepareKey(static::getKey()), $value, MCRYPT_MODE_ECB);
    }
}