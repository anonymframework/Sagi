<?php

namespace Sagi\Database\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrationCreateFormat
 * @package Sagi\Database\Console
 */
class MigrationCreateFormat extends Command
{
    protected function configure()
    {
        $this->setName('migration:create')
            ->setDescription('create a new migration file')
            ->addArgument('file', InputArgument::REQUIRED, 'the name of file');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');


    }
}