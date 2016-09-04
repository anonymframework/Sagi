<?php

namespace Sagi\Database\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class SeedAllFileCommand extends Command
{

    protected function configure()
    {
        return $this->setName('seed:all')->setDescription('runs all seed files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}