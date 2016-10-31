<?php
    /**
     * Bu Dosya AnonymFramework'e ait bir dosyadır.
     *
     * @author vahitserifsaglam <vahit.serif119@gmail.com>
     * @see http://gemframework.com
     *
     */

    namespace Sagi\Http;
    use Exception;

    /**
     * Class HttpResponseException
     * @package Anonym\Http
     */
    class HttpResponseException extends Exception
    {

        /**
         * Sınıfı başlatır ve istisnayı oluşturur
         *
         * @param string $message
         */
        public function __construct($message = '')
        {
            $this->message = $message;
        }
    }
