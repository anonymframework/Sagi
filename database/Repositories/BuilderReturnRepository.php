<?php

namespace Sagi\Database\Repositories;


class BuilderReturnRepository
{

    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $args;

    public function __construct($content, array  $args = [])
    {
        $this->content = $content;
        $this->args = $args;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return BuilderReturnRepository
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return BuilderReturnRepository
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }
}
