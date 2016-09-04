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

class CreateSeedCommand extends Command
{
    protected function configure()
    {
        $this->setName('seed:create')
            ->setDescription('create a new seed file')
            ->addArgument('name', InputArgument::REQUIRED, 'the name of file');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $seeder = new Seeder($output);
        $name = $input->getArgument('name');


        $path = $seeder->prepareSeedFile($name);

        $content = TemplateManager::prepareContent('seed', [
            'name' => MigrationManager::prepareClassName($seeder->prepareSeedName($name))
        ]);

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