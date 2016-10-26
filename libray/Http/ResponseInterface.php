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
     * Interface ResponseInterface
     * @package Anonym\Http
     */
    interface ResponseInterface
    {
        /**
         * İçeriği gönderir
         *
         * @return bool
         */
        public function send();
    }
