<?php

namespace Sagi\Database\Console;

use Sagi\Database\ConfigManager;
use Sagi\Database\Model;
use Sagi\Database\QueryBuilder;
use Sagi\Database\TableMapper;
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
        $tableName = $name;

        $columns = QueryBuilder::createNewInstance()->query("SHOW COLUMNS FROM `$name`")->fetchAll();


        $fields = array_column($columns, 'Field');

        $timestamps = $this->findTimestamps($fields);

        $content = TemplateManager::prepareContent('model', [
            'table' => $name,
            'relations' => ConfigManager::get('prepare_relations',
                true) === true ? $this->prepareRelations($name) . $this->prepareRelationsMany($name) : '',
            'name' => $name = MigrationManager::prepareClassName($name),
            'primary' => $this->findPrimaryKey($columns),
            'timestamps' => $timestamps,
            'abstract' => $abstract = $name . 'Abstract',
        ]);

        $setter = $this->prepareModelSetters($tableName);
        $abstractContent = TemplateManager::prepareContent('abstract.model', [
            'methods' => $setter[0],
            'property' => $setter[1],
            'fake_methods' => $setter[2],
            'name' => $abstract
        ]);

        $path = 'models/' . $name . '.php';
        $abstractPath = 'models/Abstraction/' . $abstract . '.php';

        $force = $input->getArgument('force');


        if (file_exists($path) && $force == "force") {
            unlink($path);

            if (file_exists($abstractPath)) {
                unlink($abstractPath);
            }
        }

        if (!file_exists($path)) {
            if (file_put_contents($path, $content) && file_put_contents($abstractPath, $abstractContent)) {
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
     * @param $name
     * @return string
     */
    private function prepareRelationsMany($name)
    {
        if (isset($this->relations->many->$name)) {
            $relate = (array)$this->relations->many->$name;

            $table = array_keys($relate)[0];

            $ourCol = $relate[$table][0];
            $tarCol = $relate[$table][1];

            $content = $this->prepareOne($table, $ourCol, $tarCol, true);

            return $content;
        }


        return '';

    }

    private function prepareModelSetters($name)
    {

        $string = '';
        $pp = '';
        $method = '';

        $tables = new TableMapper();

        $table = $tables->mapTable([$name]);

        foreach ($table->columns as $column) {

            $setterMethodName = "set" . MigrationManager::prepareClassName($column->name);
            $getterMethodName = "get" . MigrationManager::prepareClassName($column->name);

            $type = $column->type;


            if ($type === "tinyint" || $type === "bigint" || $type === "int") {
                $type = "int";
            } elseif ($type === "decimal" || $type === "float") {
                $type = "float";
            } else {
                $type = "string";
            }

            $methodName = 'filterBy' . MigrationManager::prepareClassName($column->name);
            $method .= $this->addMethod($methodName, $column->name, $type);
            $pp .= $this->addProperty($column->name, $type);
            $string .= $this->prepareSetterMethod($setterMethodName, $column->name, $type);
            $string .= $this->prepareGetterMethod($getterMethodName, $type);
        }

        $method = rtrim($method, PHP_EOL);
        $pp = rtrim($pp, PHP_EOL);

        if ($pp === '') {
            $pp = '*';
        }

        if ($method === '') {
            $method = '';
        }

        return [$string, $pp, $method];
    }

    /**
     * @param $method
     * @param $column
     * @param $type
     * @return string
     */
    private function addMethod($method, $column, $type)
    {
        return ' *@method $this ' . $method . '(' . $type . ' $' . $column . ')' . PHP_EOL;
    }

    /**
     * @param $column
     * @param $type
     * @return string
     */
    private function addProperty($column, $type)
    {
        return ' *@property $' . $column . ' ' . $type . PHP_EOL;
    }

    /**
     * @param $methodName
     * @param $column
     * @param $type
     * @return string
     */
    private function prepareSetterMethod($methodName, $column, $type)
    {
        return ' *@method $this ' . $methodName . '(' . $type . ' $' . $column . ')' . PHP_EOL;
    }

    /**
     * @param $methodName
     * @param $column
     * @param $type
     * @return string
     */
    private function prepareGetterMethod($methodName, $type)
    {
        return ' *@method ' . $type . ' ' . $methodName . '()' . PHP_EOL;

    }

    private function prepareOne($table, $tarCol, $ourCol, $many = false)
    {
        $class = MigrationManager::prepareClassName($table);


        $command = $many === false ? '$this->hasOne' : '$this->hasMany';
        return <<<CODE
     /**
      * 
      * @return $class
      */
      public function $table(){
          return $command($class::className(), ['$ourCol', '$tarCol']);       
       }

CODE;
    }

    private function prepareRelations($name)
    {
        $ret = '';

        if (isset($this->relations->one->$name)) {
            $relate = (array)$this->relations->one->$name;

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

                    $count = count($field[0]);
                    for ($i = 0; $i < $count; $i++) {
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

        $pattern = $count === 1 ? '%s' : $count > 1 ?  '[%s]' : '';

        $pri = rtrim($pri, ',');

        return sprintf($pattern, $pri);
    }
}
