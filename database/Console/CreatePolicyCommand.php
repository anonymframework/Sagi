<?php

namespace Sagi\Database\Console;

use Sagi\Database\TemplateManager;
use Sagi\Database\MigrationManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePolicyCommand extends Command
{
    protected function configure()
    {
        $this->setName('policy:create')
            ->setDescription('create a new migration file')
            ->addArgument('file', InputArgument::REQUIRED, 'the name of file');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        $model = ucfirst($file);
        $name = $model . 'Policy';

        $fileName = 'policies'. DIRECTORY_SEPARATOR . $name .'.php';

        if (!file_exists($fileName)) {

            if (touch($fileName)) {
                $put = file_put_contents($fileName, $content = TemplateManager::prepareContent('policy', ['name' => $name, 'model' => $model, 'variable' => $file]));
                if ($put) {
                    $output->writeln('<info>' . $fileName . ' : policy created successfully</info>');
                } else {
                    $output->writeln('<error>' . $fileName . ' : policy could not created</error>');

                }
            } else {
                $output->writeln('<error>' . $fileName . ' : policy could not created</error>');
            }
        } else {
            $output->writeln('<error>' . $fileName . ' : already exists</error>');

        }
    }
}