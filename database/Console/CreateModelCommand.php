<?php

namespace Sagi\Database\Console;

use Sagi\Database\QueryBuilder;
use Sagi\Database\TemplateManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Sagi\Database\MigrationManager;

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
            'table' => $name,
            'name' => $name = MigrationManager::prepareClassName($name),
            'fields' => $this->prepareFields($fields),
            'primary' => $primary = $this->findPrimaryKey($columns),
            'timestamps' => $timestamps
        ]);


        $path = 'models/' . $name . '.php';


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

    /**
     * @param $fields
     * @return array|bool
     */
    private function findTimestamps($fields)
    {
        $timestamps = [];

        if (in_array('created_at', $fields)) {
            $timestamps[] = "'created_at'";
        }

        if (in_array('updated_at', $fields)) {
            $timestamps[] = "'updated_at'";
        }

        return empty($timestamps) ? 'false' : '[' . join(',', $timestamps) . ']';
    }

    private function prepareFields($fields)
    {
        $fields = array_map(function ($value) {
            return "'$value'";
        }, $fields);


        return join(',', $fields);
    }

    /**
     * @param $array
     * @return mixed
     */
    private function findPrimaryKey($array)
    {
        foreach ($array as $item) {
            if ($item['Key'] === 'PRI') {
                return "'{$item['Field']}'";

            }
        }

        return "'id'";
    }
}