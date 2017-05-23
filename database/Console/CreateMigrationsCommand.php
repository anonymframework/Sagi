<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database\Console;

use Sagi\Database\Mapping\Column;
use Sagi\Database\MigrationManager;
use Sagi\Database\TableMapper;
use Sagi\Database\TemplateManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class CreateMigrationsCommand extends Command
{

    protected function configure()
    {
        $this->setName('migration:all')->setDescription('creates migrations file from your database1');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mapper = new TableMapper();

        $mapped = $mapper->map();


        foreach ($mapped as $table) {

            if (in_array($table->name, MigrationManager::$systemMigrations)) {
                continue;
            }

            $create = '$this->createTable("'.$table->name.'", function(Table $table){'."\n\r";

            foreach ($table->columns as $column) {

                /**
                 * @var Column $column
                 */

                if ($column->type === "varchar") {
                    $type = "string";
                } elseif ($column->primaryKey === true) {
                    $type = "pk";
                }

                $create .= "\t\t".'$table->'.$type.'("'.$column->name.'"';

                if ($column->length) {
                    $create .= ','.$column->length;
                }

                $create .= ')';

                if ($column->default !== null) {
                    if ($column->default === 'CURRENT_TIMESTAMP') {
                        $create .= '->defaultExpression("CURRENT_TIMESTAMP")';
                    } else {
                        $create .= '->defaultValue("'.$column->default.'")';
                    }
                } else {
                    if ($column->nullable === true) {
                        $create .= "->null()";
                    } else {
                        $create .= "->notNull()";
                    }
                }


                $create .= "; \n";

            }

            $create .= '});';

            $drop = '$this->dropTable("'.$table->name.'");'."\n";

            $manager = TemplateManager::prepareContent(
                'migration',
                [
                    'name' => MigrationManager::prepareClassName('create_'.$table->name.'_table'),
                    'up' => $create,
                    'down' => $drop,
                ]
            );

            $fileName = MigrationManager::migrationPath($table->name);

            if ( ! file_exists($fileName)) {

                if (touch($fileName)) {
                    $put = file_put_contents($fileName, $manager);

                    if ($put) {
                        $output->writeln('<info>'.$fileName.' : migration created successfully</info>');
                    } else {
                        $output->writeln('<error>'.$fileName.' : migration could not created</error>');

                    }
                } else {
                    $output->writeln('<error>'.$fileName.' : migration could not created</error>');
                }
            } else {
                $output->writeln('<error>'.$fileName.' : already exists</error>');

            }
        }
    }
}
