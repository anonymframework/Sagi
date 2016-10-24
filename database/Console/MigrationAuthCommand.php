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
        $this->setName('migration:auth')
            ->setDescription('creates auth migration');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        $fileName = MigrationManager::migrationPath($file);

        if (!file_exists($fileName)) {

            if (touch($fileName)) {
                $put = file_put_contents($fileName, $content = TemplateManager::prepareContent('auth', []));

                if ($put) {
                    $output->writeln('<info>' . $fileName . ' : migration created successfully</info>');
                } else {
                    $output->writeln('<error>' . $fileName . ' : migration could not created</error>');

                }
            } else {
                $output->writeln('<error>' . $fileName . ' : migration could not created</error>');
            }
        } else {
            $output->writeln('<error>' . $fileName . ' : already exists</error>');

        }


    }


}