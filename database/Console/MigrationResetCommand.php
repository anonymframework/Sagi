<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 26.08.2016
 * Time: 23:02
 */

namespace Sagi\Database\Console;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class MigrationResetCommand extends Command
{
    protected function configure()
    {
        $this->setName('migration:reset')
            ->setDescription('reset migration files');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->find('migration:drop')->run($input, $output);
        $this->getApplication()->find('migrate')->run($input, $output);
    }
}
