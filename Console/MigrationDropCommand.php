<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 26.08.2016
 * Time: 23:02
 */

namespace Sagi\Database\Console;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sagi\Database\MigrationManager;
use Symfony\Component\Console\Command\Command;

class MigrationDropCommand extends Command
{
    protected function configure()
    {
        $this->setName('migrate')
            ->setDescription('run migration files');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migration = new MigrationManager();


        $files = $migration->down();

        foreach ($files as $file) {
            $output->writeln("<info>" . $file . " dopped successfully.</info>");
        }
    }
}