<?php

namespace Sagi\Database;


class SessionManager
{

    protected static $started = false;


    public static function set($name, $value, $time = 2628000)
    {
        if (!static::$started) {
            static::sessionStart();
        }

        if (is_array($value) or is_object($value)) {
            $value = serialize($value) . '__serialized';
        }

        $value = serialize([
            'expiration' => time() + $time,
            'value' => $value
        ]);


        $value = base64_encode(CryptManager::encode(base64_encode($value)));


        $_SESSION[$name] = $value;

        return true;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        if (!static::$started) {
            static::sessionStart();
        }

        return isset($_SESSION[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        if (!static::$started) {
            static::sessionStart();
        }

        if (!static::has($name)) {
            return false;
        }

        $value = base64_decode(CryptManager::decode(base64_decode($_SESSION[$name])));

        $value = unserialize($value);


        if ($value['expiration'] < time()) {
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
        if (!static::$started) {
            static::sessionStart();
        }

        unset($_SESSION[$name]);

        return true;
    }

    /**
     * @return void
     */
    public static function flush()
    {
        if (!static::$started) {
            static::sessionStart();
        }

        $_SESSION = [];
    }

    /**
     *
     */
    protected static function sessionStart()
    {
        session_start();

        static::$started = true;
    }

}