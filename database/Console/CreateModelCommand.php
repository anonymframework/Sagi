<?php

namespace Sagi\Database\Console;

use Sagi\Database\ColumnMapper;
use Sagi\Database\ConfigManager;
use Sagi\Database\Model;
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
            ->addArgument('name', InputArgument::REQUIRED, 'the name of file')
            ->addArgument('force', InputArgument::OPTIONAL, 'force for delete old models');

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

        $mapper = new ColumnMapper();
        $mapped = $mapper->map($name);


        $content = TemplateManager::prepareContent('model', [
            'table' => $name,
            'relations' => ConfigManager::get('prepare_relations', true) === true ?  $this->prepareRelations($mapped, $name).$this->prepareRelationsMany($name): '',
            'name' => $name = MigrationManager::prepareClassName($name),
            'fields' => $this->prepareFields($mapped, $name),
            'primary' => $primary = $this->findPrimaryKey($columns),
            'timestamps' => $timestamps,
        ]);


        $path = 'models/' . $name . '.php';
        $force = $input->getArgument('force');


        if (file_exists($path) && $force == "force") {
            unlink($path);
        }

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

        if (in_array(Model::CREATED_AT, $fields)) {
            $timestamps[] = "'created_at'";
        }

        if (in_array(Model::UPDATED_AT, $fields)) {
            $timestamps[] = "'updated_at'";
        }

        return empty($timestamps) ? 'false' : '[' . join(',', $timestamps) . ']';
    }

    /**
     * @param $fields
     * @return string
     */
    private function prepareFields($fields)
    {
        $fields = array_map(function ($value) {
            return "'$value->name'";
        }, $fields);

        return join(',', $fields);
    }

    private function prepareRelationsMany($name)
    {

        $subname = $name.'_id';

        $tables = QueryBuilder::createNewInstance()->query('SHOW TABLES')->fetchAll();

        $content = '';

        foreach ($tables as $table){
            $table = $table[0];

            if($table == $name){
                continue;
            }


            if(in_array($table, MigrationManager::$systemMigrations)){
                continue;
            }

            $columns  = QueryBuilder::createNewInstance()->query("SELECT $subname FROM `$table`");

            if(!$columns)
            {
                continue;
            }

            if($configs = ConfigManager::get('relations.'.$table, 'many')){
                $command = '$this->has'.ucfirst($configs);
            }

            $function = lcfirst(MigrationManager::prepareClassName($table));
            $class = MigrationManager::prepareClassName($table);


            $content .= <<<MANY
    /**
     *
     * @return \Sagi\Database\RelationShip
     */
      public function $function(){
            return $command($class::className(), ["id", '$subname' ]);
      }
      

MANY;

        }

        return $content;

    }

    private function prepareRelations($fields, $name)
    {
        $keys = QueryBuilder::createNewInstance()
            ->getPdo()->query("SHOW CREATE TABLE `$name`")->fetch(\PDO::FETCH_OBJ);

        var_dump($keys);
        exit();
    }

    /**
     * @param $array
     * @return mixed
     */
    private function findPrimaryKey($array)
    {
        $pri = '';

        $count = 0;
        foreach ($array as $item) {
            if ($item['Key'] === 'PRI') {
                $count += 1;
                $pri .=  "'{$item['Field']}',";
            }
        }

        $pattern = $count === 1 ? '%s' : '[%s]';

        $pri = rtrim($pri, ',');

        $pri === '' ?: "'id'";

        return sprintf($pattern, $pri);
    }
}