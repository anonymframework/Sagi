<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 24.08.2016
 * Time: 15:22
 */

namespace Sagi\Database;

/**
 * Class Command
 * @package Sagi\Database
 */
class Command
{

    /**
     * @var array
     */
    protected $patterns =
        [
            'auto_increment' => [
              'string' => 'PRIMARY KEY AUTO_INCREMENT',
                'belongs_to' => 'increment'
            ],
            'default' => [
                'string' => 'DEFAULT %s',
                'belongs_to' => 'default',
            ],
            'null' => [
                'string' => 'NULL',
                'belongs_to' => 'null'
            ],

            'notnull' => [
                'string' => 'NOT NULL',
                'belongs_to' => 'null'
            ],
            'unique' => [
                'string' => 'UNIQUE',
                'belongs_to' => 'unique',
            ],
            'unsigned' => [
                'string' => 'UNSIGNED',
                'belongs_to' => 'signed'
            ],
            'signed' => [
                'string' => 'SIGNED',
                'belongs_to' => 'signed'
            ],
            'constraint' => [
                'string' => 'CONSTRAINT %s',
                'belongs_to' => 'substring'
            ],
            'delete_cascade' => [
                'string' => 'ON DELETE CASCADE',
                'belongs_to' => 'end'
            ],
            'update_cascade' => [
                'string' => 'ON UPDATE CASCADE',
                'belongs_to' => 'end'
            ]
        ];

    /**
     * @var array
     */
    protected $string = [
        'default',
        'null',
        'notnull',
        'unique'
    ];

    /**
     * @var array
     */
    protected $integer = [
        'default',
        'null',
        'notnull',
        'signed',
        'unsigned',
        'auto_increment'
    ];

    /**
     * @var array
     */
    protected $other = [
        'constraint',
        'delete_cascade',
        'update_cascade'
    ];

    /**
     * @var array
     */
    protected $queires = [];

    /**
     * @var string
     */
    protected $patternString;

    /**
     * @var string
     */
    protected $selectedType;

    /**
     * @var array
     */
    protected $patternNeeds;
    /**
     * Command constructor.
     * @param string $command
     * @param string $pattern
     * @param string $type
     */
    public function __construct($command, $pattern = '', $type = 'string')
    {

        $this->selectedType = $type;
        $this->patternString = $pattern;

        $this->patternNeeds = explode(' ', $this->patternString);

        $this->fillNeededAttributes();

        $this->queires['command'] = [$command];
    }

    protected function fillNeededAttributes(){
        foreach ($this->patternNeeds as $need){
            $need = str_replace(':', '', $need);

            $this->queires[$need] = [''];
        }
    }

    /**
     *
     */
    public function autoIncrement(){
        $this->addCommand('auto_increment', []);
    }
    /**
     * @param string $name
     * @return Command
     */
    public function constraint($name)
    {
        return $this->addCommand('constraint', [$name]);
    }

    /**
     * @return Command
     */
    public function null()
    {
        return $this->addCommand('null', []);
    }

    /**
     * @return Command
     */
    public function unique()
    {
        return $this->addCommand('unique', []);
    }

    /**
     * @return Command
     */
    public function signed()
    {
        return $this->addCommand('signed', []);
    }

    /**
     * @return Command
     */
    public function unsigned()
    {
        return $this->addCommand('unsigned', []);
    }

    /**
     * @return Command
     */
    public function notNull()
    {
        return $this->addCommand('notnull', []);
    }

    /**
     * @return Command
     */
    public function onUpdateCascade()
    {
        return $this->addCommand('update_cascade', []);
    }

    /**
     * @return Command
     */
    public function onDeleteCascade()
    {
        return $this->addCommand('delete_cascade', []);
    }

    /**
     * @param $value
     * @return Command
     */
    public function defaultValue($value)
    {
        return $this->addCommand('default', ["'" . $value . "'"]);
    }

    /**
     * @param $expression
     * @return Command
     */
    public function defaultExpression($expression)
    {
        return $this->addCommand('default', [$expression]);
    }

    /**
     * @return Command
     */
    public function currentTimestamp()
    {
        return $this->defaultExpression('CURRENT_TIMESTAMP');
    }

    /**
     * @param $type
     * @return bool
     */
    public function isAllowed($type)
    {
        $selected = $this->selectedType;

        return array_search($type, $this->$selected);
    }

    /**
     * @param $type
     * @param $variables
     * @param bool $unshift
     * @return $this
     * @throws SchemaException
     */
    private function addCommand($type, $variables)
    {
        if (!$this->isAllowed($type) !== false) {
            throw new SchemaException(sprintf('%s command is not allowed on a %s typed method, allows (%s) ', $type, $this->selectedType, join(',', $this->{$this->selectedType})));
        }

        if (!empty($variables)) {
            array_unshift($variables, $this->patterns[$type]['string']);

            $command = call_user_func_array('sprintf', $variables);
        } else {
            $command = $this->patterns[$type]['string'];
        }


        $this->queires[$this->patterns[$type]['belongs_to']][] = $command;

        return $this;
    }

    /**
     * @return string
     */
    public function prepareCommand()
    {
        $pattern = $this->patternString;


        foreach ($this->queires as $search => $values) {

            $search = trim($search);
            $values = join(' ', $values);

            $pattern = str_replace(':' . $search, $values, $pattern);
        }

        $pattern =  join(' ', array_filter(explode(' ', $pattern), function ($value){
            if (!empty($value)) {
                return $value;
            }else{
                return false;
            }
        }));

        return $pattern;
    }
}