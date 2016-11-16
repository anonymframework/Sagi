<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 26.08.2016
 * Time: 18:35
 */

namespace Sagi\Database\Console;

use Sagi\Database\MigrationManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Class MigrateCommand
 * @package Sagi\Database\Console
 */
class MigrateCommand extends Command
{

    protected function configure()
    {
        $this->setName('migrate')
            ->setDescription('run migration files');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migration = new MigrationManager();


        $files = $migration->migrate();

        foreach ($files as $file) {
            if($file['status'] == 1){
                $output->writeln("<info>" . $file['name'] . " migrated.</info>");
            }elseif($file['status'] == 2){
                $output->writeln('<error>'.$file['name'].' already exists </error>');
            }
        }
    }

}