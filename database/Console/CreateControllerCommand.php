<?php

namespace Sagi\Database\Console;

use Sagi\Database\QueryBuilder;
use Sagi\Database\Seeder;
use Sagi\Database\TemplateManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Sagi\Database\MigrationManager;

class CreateControllerCommand extends Command
{
    protected function configure()
    {
        $this->setName('controller:create')
            ->setDescription('create a new controller file')
            ->addArgument('name', InputArgument::REQUIRED, 'the name of file');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');



        $content = TemplateManager::prepareContent('controller', [
            'name' => $fileName = MigrationManager::prepareClassName($name)
        ]);


        $path = "app/controllers/".$fileName.'.php';


        if (!file_exists($path)) {
            if (file_put_contents($path, $content)) {
                $output->writeln("<info>" . $name . ' created successfully in ' . $path . "</info>");
            } else {
                $output->writeln("<error>" . $name . ' couldnt create in ' . $path . "</error>");

            }
        } else {
            $output->writeln("<error>" . $name . ' already exists in ' . $path . "</error>");
        }
    }
}