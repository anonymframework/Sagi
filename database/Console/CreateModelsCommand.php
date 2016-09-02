<?php
namespace Sagi\Database\Console;


use Sagi\Database\MigrationManager;
use Sagi\Database\QueryBuilder;
use Sagi\Database\TemplateManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModelsCommand extends Command
{

    protected $expects = ['migrations'];

    protected function configure()
    {
        $this->setName('model:all')->setDescription('auto create model files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!is_dir('models')) {
            mkdir('models', 0777);
        }

        $tables = QueryBuilder::createNewInstance()->query("SHOW TABLES LIKE ''")->fetchAll();

        $tables = array_map(function ($value) {
            $value = array_values($value)[0];

            return $value;
        }, $tables);

        foreach ($tables as $table) {

            if (in_array($table, $this->expects)) {
                continue;
            }

            $this->getApplication()->find('model:create')->run(new ArrayInput(array('name' => $table)), $output);
        }
    }


}
