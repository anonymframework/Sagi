<?php
/**
 * Bu Dosya AnonymFramework'e ait bir dosyadır.
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 */

namespace Sagi\Http;

/**
 * Class Input
 * @package Anonym\Http
 */
class Input
{
    /**
     * $name' e atanan veriye göre $_POST da veri varmı yokmu onu kontrol eder
     *
     * @param string $name
     * @return boolean
     */
    public  function has($name = null)
    {
        if (isset($_POST)) {
            return isset($_POST[$name]);
        }
    }

    /**
     * $name'in $_POST içinde var olup olmadığına bakmazsızın veriyi çağırır
     *
     * @param string $name
     * @return mixed
     */
    public  function get($name)
    {
        return $_POST[$name];
    }

    /**
     * $_POST içinde $name'e $value' i atar;
     *
     * @param string $name
     * @param mixed $value
     */
    public  function set($name, $value)
    {
        $_POST[$name] = $value;
    }

    /**
     * $_GET içinden $name'in değerini siler
     *
     * @param string $name
     */
    public  function delete($name)
    {
        unset($_POST[$name]);
    }

    /**
     * @return mixed
     *
     * Post verilerini döndürür
     */
    public  function getAll()
    {
        if (isset($_POST)) {
            return $_POST;
        } else {
            return false;
        }
    }
}
