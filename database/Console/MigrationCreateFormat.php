<?php
namespace Sagi\Database\Console;

use Sagi\Database\MigrationManager;
use Sagi\Database\TemplateManager;
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
                $put = file_put_contents($fileName, TemplateManager::prepareContent('migration', [
                    'name' => MigrationManager::prepareClassName($file),
                    'up' => '',
                    'down' => ''
                ]));

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