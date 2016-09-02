<?php
namespace Sagi\Database;


class CookieManager
{

    /**
     * @param string $name
     * @param mixed $value
     * @param int $time
     * @return bool
     */
    public static function set($name, $value, $time = 3600)
    {
        if (is_array($value) or is_object($value)) {
            $value = serialize($value) . '__serialized';
        }

        return setcookie($name, $value, time() + $time);
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

        $value = $_COOKIE[$name];

        if (strpos($value, $explode = "__serialized") !== false) {
            $value = explode($explode, $value)[0];

            return unserialize($value);
        }

        return $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function delete($name)
    {
        return setcookie($name, '', time() - 3600);
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
}
