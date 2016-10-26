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
 * Bu sınıf AnonymFramework' de kullanılmak üzere tasarlanmıştır, Json Response leri oluşturmak üzere tasarlanmıştır
 * Class JsonResponse
 *
 * @package Anonym\Http
 */
class JsonResponse extends Response
{
    /**
     * @param string $content
     * @param int $statusCode
     */
    public function __construct($content = '', $statusCode = 200)
    {
        parent::__construct($content, $statusCode);
        $this->setContentType('application/json');

    }

    /**
     * Yeni bir instance oluşturur
     *
     * @param string $content
     * @param int $statusCode
     * @return static
     */
    public static function create($content = '', $statusCode = 200)
    {
        return new static($content, $statusCode);
    }
}
