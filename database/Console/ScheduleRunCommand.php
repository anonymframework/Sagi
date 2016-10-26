<?php

namespace Sagi\Database\Console;


use Sagi\Database\MigrationManager;
use Sagi\Database\Schema;
use Sagi\Database\TemplateManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrationCreateFormat
 * @package Sagi\Database\Console
 */
class MigrationAuthCommand extends Command
{

    protected function configure()
    {
        $this->setName('schedule:run')
            ->setDescription('Runs all scheduled commands');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {


    }


}