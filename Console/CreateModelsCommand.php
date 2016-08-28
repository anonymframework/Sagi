<?php
namespace Sagi\Database\Console;


use Sagi\Database\MigrationManager;
use Sagi\Database\QueryBuilder;
use Sagi\Database\TemplateManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModelsCommand extends Command
{

    protected function configure()
    {
        $this->setName('create:models')->setDescription('auto create model files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tables = QueryBuilder::createNewInstance()->query("SHOW TABLES LIKE ''")->fetchAll();

        $tables = array_map(function ($value) {
            $value = array_values($value)[0];

            return $value;
        }, $tables);

        foreach ($tables as $table) {
            $content = TemplateManager::prepareContent('model', [
                'table' => $table,
                'name' => $name = MigrationManager::prepareClassName($table)
            ]);

            $path = 'models/' . $name . '.php';

            if(!file_exists($path)){
                if (file_put_contents($path, $content)) {
                    $output->writeln("<info>". $name . ' created successfully in ' . $path. "</info>");
                }
            }else{
                $output->writeln("<error>".$name . ' already exists in ' . $path."</error>");

            }


        }

    }
}
