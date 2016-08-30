<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 25.08.2016
 * Time: 19:34
 */

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
    public static $jsonFile = "../config.php";


    public static function loadConfigs()
    {
        if (file_exists(static::$jsonFile)) {
            static::$configs = include static::$jsonFile;
        }else{
            throw new ConfigException(static::$jsonFile. 'is not exists');
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
    public static function get($key)
    {
        $results = [];

        $array = static::getConfigs();

        foreach (explode('.', $key) as $segment) {
            foreach ($array as $value) {
                if (array_key_exists($segment, $value = (array)$value)) {
                    $results[] = $value[$segment];
                }
            }
            $array = array_values($results);
        }

        return array_values($results);
    }

    /**
     * @param array $configs
     */
    public static function setConfigs($configs)
    {
        self::$configs = $configs;
    }


}