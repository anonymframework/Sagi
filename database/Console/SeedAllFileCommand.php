<?php

namespace Sagi\Database\Console;

use Sagi\Database\Seeder;
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
        $seeder = new Seeder($output);

        $glob = glob($seeder->getSeedPath() . '/*.php');


        foreach ($glob as $file) {

            $explode = explode("/", $file);

            $nameExplode = explode(".php", $explode[1]);

            $name = explode("__", $nameExplode)[1];

            $seeder->seed($name);
        }

    }
}
