<?php

namespace Sagi\Database\Console;


use Sagi\Database\Loggable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanLogsCommand extends Command
{
    protected function configure()
    {
        $this->setName('log:clean')->setDescription('Clean All Logs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = glob(Loggable::$logFile.DIRECTORY_SEPARATOR.'*.log');

        foreach($files as $file){

        }

        $output->write('Success!');
    }
}