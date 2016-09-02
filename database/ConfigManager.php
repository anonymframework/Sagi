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


    public static function loadConfigs()
    {
        static::$configFile = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.php";

        if (file_exists(static::$configFile)) {
            static::$configs = include static::$configFile;
        }else{
            throw new ConfigException(static::$configFile. 'is not exists');
        }
    }

    /**
     * @return array
     */
    public static function getConfigs()
    {
        if (!static::$configs) {
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
        $results = [];

        $array = static::getConfigs();

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

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