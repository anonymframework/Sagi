<?php

namespace Sagi\Database\Console;

use Sagi\Database\QueryBuilder;
use Sagi\Database\TemplateManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class CreateModelCommand extends Command
{
    protected function configure()
    {
        $this->setName('model:create')
            ->setDescription('create a new model file')
            ->addArgument('name', InputArgument::REQUIRED, 'the name of file');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        $columns = QueryBuilder::createNewInstance()->query("SHOW COLUMNS FROM `$name`")->fetchAll();


        $fields = array_column($columns, 'Field');

        $timestamps = $this->findTimestamps($fields);

        $content = TemplateManager::prepareContent('model', [
            'table' => $table,
            'name' => $name = MigrationManager::prepareClassName($table),
            'fields' => $this->prepareFields($fields),
            'primary' => $primary = $this->findPrimaryKey($columns),
            'timestamps' => $timestamps
        ]);

        $class = ucfirst($name);


        $path = 'models/' . $class . '.php';


        if (!file_exists($path)) {
            if (file_put_contents($path, $content)) {
                $output->writeln("<info>" . $class . ' created successfully in ' . $path . "</info>");
            } else {
                $output->writeln("<error>" . $class . ' couldnt create in ' . $path . "</error>");

            }
        } else {
            $output->writeln("<error>" . $class . ' already exists in ' . $path . "</error>");
        }
    }
}