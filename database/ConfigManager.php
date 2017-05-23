<?php

namespace Sagi\Database;


class ConfigManager
{

    /**
     * @var array
     */
    public static $configs;

    /**
     * @var string
     */
    public static $configFile;

    /**
     * @return array
     */
    public static function returnDefaultConnection()
    {
        $connection = self::get('connections.default', []);


        return $connection;
    }


    public static function loadConfigs()
    {
        $rootDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;

        static::$configFile = $rootDir."config.php";

        if (file_exists(static::$configFile)) {
            static::$configs = include static::$configFile;

            static::set('root_dir', $rootDir);
        } else {
            throw new Exceptions\ConfigException(static::$configFile.'is not exists');
        }
    }

    /**
     * @return array
     */
    public static function getConfigs()
    {
        if ( ! static::$configs) {
            static::loadConfigs();
        }

        return self::$configs;
    }

    /**
     * Fetch a flattened array of a nested array element.
     *
     * @param  string $key
     * @return array
     *
     */
    public static function get($key, $default = null)
    {
        $array = static::getConfigs();

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if ( ! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public static function set($key, $value)
    {
        $array = &static::$configs;

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * @param array $configs
     */
    public static function setConfigs($configs)
    {
        self::$configs = $configs;
    }
}
