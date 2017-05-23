<?php

namespace Sagi\Database\Console;

use Sagi\Database\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class CreateModelsCommand extends Command
{

    protected $expects = ['migrations'];

    protected function configure()
    {
        $this->setName('model:all')->setDescription('auto create model files')
            ->addArgument('force', InputArgument::OPTIONAL, 'force for delete old models');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ( ! is_dir('models')) {
            mkdir('models', 0777);
        }

        $tables = QueryBuilder::createNewInstance()->query("SHOW TABLES LIKE ''")->fetchAll();

        $tables = array_map(
            function ($value) {
                $value = array_values($value)[0];

                return $value;
            },
            $tables
        );

        $force = $input->getArgument('force');


        if ($force !== 'force') {
            $force = 'standart';
        }


        foreach ($tables as $table) {

            if (in_array($table, $this->expects)) {
                continue;
            }

            $this->getApplication()->find('model:create')->run(
                new ArrayInput(array('name' => $table, 'force' => $force)),
                $output
            );
        }
    }


}
