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

    protected $expects = ['migrations'];

    protected function configure()
    {
        $this->setName('models:all')->setDescription('auto create model files');
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

            $columns = QueryBuilder::createNewInstance()->query("SHOW COLUMNS FROM `$table`")->fetchAll();


            $fields = array_column($columns, 'Field');

            $timestamps = $this->findTimestamps($fields);

            $content = TemplateManager::prepareContent('model', [
                'table' => $table,
                'name' => $name = MigrationManager::prepareClassName($table),
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
