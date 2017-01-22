<?php

namespace Sagi\Database\Console;


use Sagi\Database\Seeder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeedFileCommand extends Command
{
    protected function configure()
    {
        $this->setName('seed:file')->setDescription('Seeds a file')->addArgument('name', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $seeder = new Seeder($output);

        $name = $input->getArgument('name');
        $seeder->seed($name);
    }
}
