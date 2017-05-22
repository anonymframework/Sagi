<?php

namespace Sagi\Database;

/**
 * Class TemplateManager
 * @package Sagi\Database
 */
class TemplateManager
{

    /**
     * @var string
     */
    private static $tempDir = 'database1/templates/';

    /**
     * @var string
     */
    private static $tempExt = '.temp';

    /**
     * @param string $file
     * @param array $params
     * @return bool|mixed|string
     */
    public static function prepareContent($file, $params = [])
    {
        if (static::doesTempExists($file)) {
            $content = static::getTempContent($file);

            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $content = str_replace("{{" . $key . "}}", $value, $content);
                }
            }

            return $content;
        }

        return false;
    }

    /**
     * @param $file
     * @return bool
     */
    public static function doesTempExists($file)
    {
        return file_exists(static::prepareTempPath($file));
    }

    /**
     * @param $file
     * @return string
     */
    public static function prepareTempPath($file)
    {
        return static::$tempDir . $file . static::$tempExt;
    }

    /**
     * @return string
     */
    public static function getTempContent($file)
    {
        return file_get_contents(static::prepareTempPath($file));
    }
}
