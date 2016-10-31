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
 * Class XmlResponse
 * @package Anonym\Http
 */
class XmlResponse extends Response implements ResponseInterface
{

    /**
     * Sınıfı başlatır ve içerik ve durum kodunu ayalar
     *
     * @param string $content İçerik
     * @param int $statusCode Durum kodu
     */
    public function __construct($content = '', $statusCode = 200)
    {
        parent::__construct($content, $statusCode);
        $this->setContentType('text/xml');
    }

    /**
     *
     * İçeriği gönderiri
     *
     */
    public function send()
    {
        parent::send();
    }
}
