<?php

namespace Sagi\Database;

use Symfony\Component\Console\Output\OutputInterface;

abstract class SeedManager
{

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     * @return SeedManager
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }
}
