<?php

namespace Sagi\Database;


class SesssionManager
{

    protected static $started = false;


    public static function set($name, $value, $time = 2628000)
    {
        if (is_array($value) or is_object($value)) {
            $value = serialize($value) . '__serialized';
        }

        $value = serialize([
            'expiration' => time() + $time,
            'value' => $value
        ]);

        $value = base64_encode(CryptManager::encode($value));


        $_SESSION[$name] = $value;

        return true;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        if (!static::has($name)) {
            return false;
        }

        $value = unserialize($_SESSION[$name]);

        if ($value['expiration'] > time()) {
            static::delete($name);

            return false;
        }

        if (strpos($value['value'], $explode = "__serialized") !== false) {
            $value = explode($explode, $value['value'])[0];

            return unserialize($value);
        }

        return $value['value'];
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function delete($name)
    {
        unset($_SESSION[$name]);

        return true;
    }

    /**
     * @return void
     */
    public static function flush()
    {
        $_SESSION = [];
    }

}