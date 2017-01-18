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

    protected $relations;

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

        $this->relations = json_decode(file_get_contents(dirname(__DIR__) . '/relations.json'));

        $name = $input->getArgument('name');

        $columns = QueryBuilder::createNewInstance()->query("SHOW COLUMNS FROM `$name`")->fetchAll();


        $fields = array_column($columns, 'Field');

        $timestamps = $this->findTimestamps($fields);

        $mapper = new ColumnMapper();
        $mapped = $mapper->map($name);


        $content = TemplateManager::prepareContent('model', [
            'table' => $name,
            'relations' => ConfigManager::get('prepare_relations',
                true) === true ? $this->prepareRelations($name) . $this->prepareRelationsMany($name) : '',
            'name' => $name = MigrationManager::prepareClassName($name),
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

    private function prepareRelationsMany($name)
    {
        if (isset($this->relations->many->$name)) {
            $relate = (array) $this->relations->many->$name;

            $table = array_keys($relate)[0];

            $ourCol = $relate[$table][0];
            $tarCol = $relate[$table][1];

            $content = $this->prepareOne($table, $ourCol, $tarCol, true);

            return $content;
        }


        return '';

    }

    private function prepareOne($table, $tarCol, $ourCol, $many = false)
    {
        $class = MigrationManager::prepareClassName($table);


        $command = $many == false ? '$this->hasOne' : '$this->hasMany';
        return <<<CODE
/**
      * 
      * @return mixed
      */
      function $table(){
          return $command($class::className(), ['$ourCol', '$tarCol']);       
       }

CODE;
    }

    private function prepareRelations($name)
    {
        $ret = '';

        if (isset($this->relations->one->$name)) {
            $relate = (array) $this->relations->one->$name;

            $table = array_keys($relate)[0];

            $ourCol = $relate[$table][0];
            $tarCol = $relate[$table][1];

            $ret .= $this->prepareOne($table, $ourCol, $tarCol);

            return $ret;
        } else {
            $keys = QueryBuilder::createNewInstance()
                ->prepare("SHOW CREATE TABLE `$name`", [])->fetch(\PDO::FETCH_ASSOC);


            $create = $keys['Create Table'];
            if (strpos($create, 'FOREIGN KEY') === false) {
                return '';
            } else {
                if (preg_match_all('#FOREIGN KEY \((.*?)\) REFERENCES (.*?) \((.*?)\)#si', $create, $field)) {

                    array_shift($field);

                    $field = array_map(function ($val) {
                        return array_map(function ($value) {
                            return str_replace(['`', "'", '"'], '', $value);
                        }, $val);
                    }, $field);

                    for ($i = 0; $i < count($field[0]); $i++) {
                        $ourCol = $field[0][$i];
                        $table = $field[1][$i];
                        $tarCol = $field[2][$i];

                        $ret .= $this->prepareOne($table, $tarCol, $ourCol);
                    }

                    return $ret;
                } else {
                    return '';
                }
            }
        }


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
                $pri .= "'{$item['Field']}',";
            }
        }

        $pattern = $count === 1 ? '%s' : '[%s]';

        $pri = rtrim($pri, ',');

        $pri === '' ?: "'id'";

        return sprintf($pattern, $pri);
    }
}