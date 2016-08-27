<?php
namespace Sagi\Database;

use Sagi\Database\Validation\MethodNotExistsException;
use Sagi\Database\QueryBuilder;

/**
 * Class Validation
 * @package Anonym\Validation
 */
trait Validation
{

    /**
     * an array type to rules
     *
     * @var array
     */
    protected $rules;

    /**
     * an array type to datas
     *
     * @var array
     */
    protected $datas;

    /**
     *
     *
     * @var array
     */
    protected $fails;

    /**
     * failed data messages
     *
     * @var array
     */
    protected $failedMessages;

    /**
     * ValidationErrorMessage Reposity, store the given messages
     *
     * @var array
     */
    protected $messageReposity = [];

    /**
     * story extended functions(methods)
     *
     * @var array
     */
    protected $extends;

    /**
     * store the default error messages
     *
     * @var
     */
    protected $defaultErrorMessages = [
        'required' => ':key has to be exists in your datas',
        'email' => ':key has to be a valid  email address',
        'url' => ':key has to be a valid url address',
        'json' => ':key has to be a valid json data',
        'max' => ':key value has to be lesser than :max',
        'min' => ':key value has to be bigger than :min',
        'digits_max' => ':key value has to be lesser than :max',
        'digits_min' => ':key value has to be bigger than :min',
        'same' => ':key value must be same with this(these) :same',
        'size_between' => ':key value must be between :min and :max',
        'boolean' => ':key has to be a boolean value',
        'regex' => ':key must be match with given regex value',
        'digits_between' => ':key digits size must be between :min and :max',
        'alpha' => ':key must be an alphabetical character',
        'table_exists' => ':key value is must be exists in your database',
        'column_exists' => ':key value is must be exists in your database table',

    ];

    /**
     * determine datas is correct or not
     *
     * @throws Exception
     */
    public function validate()
    {
        if (!is_array($rules = $this->getRules())) {
            $rules = $this->convertToArray($rules);
        }

        if (!is_array($datas = $this->getDatas())) {
            $datas = $this->convertToArray($datas);
        }

        foreach ($rules as $key => $rule) {
            $parsedRules = explode("|", $rule);

            foreach ($parsedRules as $parsedRule) {
                $required = $this->handleRule($parsedRule, $key, $datas);

                if ($required !== null && $required === false) {
                    break;
                }
            }
        }
    }

    /**
     * handle the given rule
     *
     * @param string $rule
     * @param string $key
     * @param array $allDatas
     * @return mixed
     */
    private function handleRule($rule, $key, array $allDatas)
    {

        $methodName = "run";

        if (!strstr($rule, "_")) {
            $methodName .= ucfirst($rule);
        } else {
            $parsedName = array_map(function ($value) {
                return ucfirst($value);
            }, explode("_", $rule));

            $methodName = $methodName . join("", $parsedName);
        }

        if (!strstr($rule, ":")) {
            $called = $this->callMethod($methodName, [$key, $allDatas, $rule]);
        } else {
            $value = explode(":", $key)[1];
            if (strstr($value, ",")) {
                $sendDatas = [explode(",", $value), $key, $allDatas, $rule];
            } else {
                $sendDatas = [$value, $key, $allDatas, $rule];
            }

            $called = $this->callMethod($methodName, $sendDatas);
        }

        return $called;

    }


    /**
     * calls method or callable function with given datas
     *
     * @param $methodName
     * @param $datas
     * @throws MethodNotExistsException
     */
    private function callMethod($methodName, $datas)
    {
        if (method_exists($this, $methodName)) {
            $call = [$this, $methodName];
        } elseif (isset($this->extends[$methodName])) {
            $call = $this->extends[$methodName];
        } else {
            throw new MethodNotExistsException(sprintf('%s method is not exists in Validation class', $methodName));
        }

        if ($methodName !== 'runRequired') {
            $return = call_user_func_array([$this, 'runRequired'], count($datas) === 4 ? array_slice($datas, 1, 4) : $datas);
        }

        call_user_func_array($call, $datas);

        return $return;
    }

    /**
     * @param string $key
     * @param array $datas
     * @param string $rule
     * @return bool
     */
    protected function runRequired($key, $datas, $rule = '')
    {
        if (!isset($datas[$key]) && !empty($datas[$key])) {
            $this->fails[] = $messageKey = "required.$key";
            $this->addMessage($key, $rule, $messageKey);
            return false;
        }

        return true;
    }

    /**
     * determine data is numeric
     *
     * @param string $key
     * @param array $datas
     * @param string $rule
     */
    protected function runNumeric($key, $datas, $rule = '')
    {

        if (!isset($datas[$key])) {
            if (!is_numeric($datas[$key])) {
                $this->fails[] = $messageKey = "numeric.$key";

                $this->addMessage($key, $rule, $messageKey);
            }
        }
    }

    /**
     * @param $between
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runDigitsBetween($between, $key, $datas, $rule)
    {
        $min = $between[0];
        $max = $between[1];

        $data = $datas[$key];

        if (is_numeric($data)) {
            $data = "$data";
        }

        $length = strlen($data);

        if ($length < $min || $length > $max) {
            $this->fails[] = $messageKey = "$rule.$key";

            $this->addMessage($key, $rule, $messageKey, [
                'min' => $min,
                'max' => $max
            ]);
        }
    }

    /**
     * determine the data is a variable alphabetic charecter
     *
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runAlpha($key, $datas, $rule)
    {
        $data = $datas[$key];

        if (!preg_match("([A-Z])", $data)) {
            $this->fails[] = $messageKey = "alpha.$key";

            $this->addMessage($key, $rule, $messageKey);
        }
    }

    /**
     * @param $max
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runMax($max, $key, $datas, $rule)
    {
        $data = $datas[$key];

        if (is_string($data)) {
            $lenght = strlen($data);
        } elseif (is_numeric($data)) {
            $lenght = $data;
        }

        if ($lenght > $max) {
            $this->fails[] = $messageKey = "max.$key";

            $this->addMessage($key, $rule, $messageKey, ['max' => $max]);
        }
    }


    /**
     * determine email is right
     *
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runEmail($key, $datas, $rule)
    {

        $email = $datas[$key];
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->fails[] = $messageKey = "email.$key";

            $this->addMessage($key, $rule, $messageKey);
        }

    }

    /**
     * @param $min
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runMin($min, $key, $datas, $rule)
    {
        $data = $datas[$key];

        if (is_string($data)) {
            $lenght = strlen($data);
        } elseif (is_numeric($data)) {
            $lenght = $data;
        }

        if ($lenght > $min) {
            $this->fails[] = $messageKey = "min.$key";

            $this->addMessage($key, $rule, $messageKey, ['min' => $min]);
        }
    }

    /**
     * @param $min
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runDigitsMin($min, $key, $datas, $rule)
    {
        $data = $datas[$key];

        if (is_string($data)) {
            $lenght = strlen("$data");
        }

        if ($lenght < $min) {
            $this->fails[] = $messageKey = "$rule.$key";

            $this->addMessage($key, $rule, $messageKey, ['min' => $min]);
        }
    }


    /**
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runArray($key, $datas, $rule)
    {
        if (!is_array($datas[$key])) {
            $this->fails[] = $messageKey = "array.$key";

            $this->addMessage($key, $rule, $messageKey);
        }
    }

    /**
     * @param $min
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runDigitsMax($max, $key, $datas, $rule)
    {
        $data = $datas[$key];

        if (is_string($data)) {
            $lenght = strlen("$data");
        }

        if ($lenght > $max) {
            $this->fails[] = $messageKey = "$rule.$key";

            $this->addMessage($key, $rule, $messageKey, ['max' => $max]);
        }
    }


    /**
     * determine if given data is a valid json data
     *
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runJson($key, $datas, $rule)
    {
        if (json_decode($datas[$key]) === false) {
            $this->fails[] = $messageKey = "json.$key";

            $this->addMessage($key, $rule, $messageKey);
        }
    }

    /**
     * determine data is correct
     *
     * @param $array
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runSizeBetween($array, $key, $datas, $rule)
    {
        $data = $datas[$key];

        $min = $array[0];
        $max = $array[1];

        if ($data < $min || $data > $max) {
            $this->fails[] = $messageKey = "$rule.$key";

            $this->addMessage($key, $rule, $messageKey, [
                'min' => $min,
                'max' => $max
            ]);
        }
    }

    /**
     * determine given regex is matching with given datas
     *
     * @param $regex
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runRegex($regex, $key, $datas, $rule)
    {
        if (!preg_match($regex, $datas[$key])) {
            $this->fails[] = $messageKey = "regex.$key";

            $this->addMessage($key, $rule, $messageKey);
        }
    }


    /**
     * determine datas are same or not
     *
     * @param $same
     * @param $key
     * @param $datas
     * @param $rule
     * @throws Exception
     */
    protected function runSame($same, $key, $datas, $rule)
    {
        if (!is_array($same)) {
            $sames = $this->convertToArray($same);
        }

        $data = $datas[$key];
        $status = true;
        foreach ($sames as $same) {
            if ($same != $data) {
                $status = false;
                break;
            }
        }

        if ($status === false) {
            $this->fails[] = $mKey = "same.$key";

            $this->addMessage($key, $rule, $mKey, [
                'not' => $same
            ]);
        }
    }

    /**
     * determine url is valid
     *
     * @param $key
     * @param $datas
     * @param $url
     */
    protected function runUrl($key, $datas, $rule)
    {
        $data = $datas[$key];

        if (!filter_var($data, FILTER_SANITIZE_URL)) {
            $this->fails[] = $messageKey = "url.$key";

            $this->addMessage($key, $rule, $messageKey);
        }
    }

    /**
     * determine the date is a valid value
     *
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runDate($key, $datas, $rule)
    {
        if (false === strtotime($datas[$key])) {
            $this->fails[] = $messageKey = "date.$key";

            $this->addMessage($key, $rule, $messageKey);
        }
    }

    /**
     * determine is table exists in database.base
     *
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runTableExists($key, $datas, $rule)
    {
        $data = $datas[$key];

        $advanced = QueryBuilder::createNewInstance()->query("SHOW TABLES LIKE '$data'");

        if (!$advanced) {
            $this->fails[] = $messageKey = "$rule.$key";

            $this->addMessage($key, $rule, $messageKey);
        }
    }

    /**
     * determine is column exists in database.base
     *
     * @param string $column
     * @param $key
     * @param $datas
     * @param $rule
     */
    protected function runColumnExists($column, $key, $datas, $rule)
    {
        $advanced = Database::table($datas[$key])->columnExists($column);

        if (!$advanced->isSuccess()) {
            $this->fails[] = $messageKey = "$rule.$key";

            $this->addMessage($key, $rule, $messageKey);
        }
    }

    /**
     * adds a error message
     *
     * @param string $key
     * @param string $rule
     * @param string $specialRule
     * @param array $datas
     */
    protected function addMessage($key, $rule, $specialRule, array $datas = [])
    {
        $specialMessages = $this->getMessageReposity();
        $defaultMessages = $this->defaultErrorMessages;

        if (count($datas) === 0) {
            $datas = [$key];
        }

        if (isset($specialMessages[$specialRule])) {
            $selectedMessage = $specialMessages[$specialRule];
        } else {
            $selectedMessage = $defaultMessages[$rule];
        }

        array_unshift($datas, $selectedMessage);
        $this->failedMessages[] = call_user_func_array('sprintf', $datas);
    }

    /**
     * tries convert given variable type to array
     *
     * @param mixed $notArray
     * @throws Exception
     * @return array
     */
    private function convertToArray($notArray)
    {
        if (is_object($notArray) || is_string($notArray) || is_numeric($notArray)) {
            return (array)$notArray;
        } else {
            throw new Exception(sprintf('your data could not convert to array'));
        }
    }

    /**
     * return the failed datas
     *
     * @return array
     */
    public function fails()
    {
        return $this->getFails();
    }

    /**
     * @return array
     */
    public function getFails()
    {
        return $this->fails;
    }

    /**
     * @param array $fails
     * @return Validation
     */
    public function setFails($fails)
    {
        $this->fails = $fails;
        return $this;
    }


    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param array $rules
     * @return Validation
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @return array
     */
    public function getMessageReposity()
    {
        return $this->messageReposity;
    }

    /**
     * @param array $messageReposity
     * @return Validation
     */
    public function setMessageReposity($messageReposity)
    {
        $this->messageReposity = $messageReposity;
        return $this;
    }

    /**
     * @return array
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * @param array $datas
     * @return Validation
     */
    public function setDatas($datas)
    {
        $this->datas = $datas;
        return $this;
    }

}
