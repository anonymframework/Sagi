<?php

namespace Sagi\Database\Console;

use Sagi\Database\MigrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class MigrationDropCommand extends Command
{
    protected function configure()
    {
        $this->setName('migration:drop')
            ->setDescription('drop migration files');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migration = new MigrationManager();


        $files = $migration->down();

        foreach ($files as $file) {
            $output->writeln("<info>".$file." dropped successfully.</info>");
        }
    }
}
