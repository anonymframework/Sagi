<?php
namespace Sagi\Database;


class CookieManager
{

    /**
     * @param string $name
     * @param mixed $value
     * @param int $time
     * @param string $path
     * @return bool
     */
    public static function set($name, $value, $time = 3600, $path = "/")
    {
        if (is_array($value) or is_object($value)) {
            $value =serialize($value) . '__serialized';
        }

        $value =  base64_encode(CryptManager::encode($value));

        return setcookie($name, $value, time() + $time, $path);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        return isset($_COOKIE[$name]);
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

        $value = CryptManager::decode(base64_decode($_COOKIE[$name]));


        if (strpos($value, $explode = "__serialized") !== false) {
            $value = explode($explode, $value)[0];

            return unserialize($value);
        }

        return $value;
    }

    /**
     * @param string $name
     * @param string $path
     * @return bool
     */
    public static function delete($name, $path = "/")
    {
        return setcookie($name, '', time() - 3600, $path);
    }

    /**
     * @return void
     */
    public static function flush()
    {
        foreach ($_COOKIE as $item => $value) {
            static::delete($item);
        }
    }

    public static function getCookies()
    {
        return $_COOKIE;
    }
}
