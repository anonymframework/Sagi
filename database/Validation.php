<?php
namespace Sagi\Database;

trait Validation
{

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $filtredDatas;

    /**
     * @var array
     */
    protected $datas;
    /**
     * @var array
     */
    protected $messages = [
        'required' => '$0 alanı doldurulması zorunludur.',
        'min' => '$0 alanına girilebilecek en küçük değer $1',
        'max' => '$0 alanına girilebilecek en büyük değer $1',
        'between' => '$0 alanına girilebilecek değerler $1 - $2 aralığında olmadılıdır.',
        'digit_min' => '$0 alanına en düşük $1 karekterli bir yazı girebilirsiniz.',
        'digit_max' => '$0 alanına en büyük $1 karekterli bir yazı girebilirsiniz.',
        'digit_betweem' => '$0 alanına $1 - $2 karekterli bir yazı girebilirsiniz.',
        'alpha' => '$0 alanına girilen değer bir a-zA-Z formatına uygun olmalıdır.',
        'numeric' => '$0 alanına girilen değer bir sayı olmalıdır.',
        'ip' => '$0 alanınına girilen değer bir ip adresine ait olmalıdır.',
        'url' => '$0 alanına girilen değer bir url adresine ait olmalıdır.',
        'email' => '$0 alanına girilen değer bir email adresine ait olmalıdır.',
        'alpha_numeric' => '$0 alanına girilen değer a-zA-Z0-9 formatına uygun olmalıdır.',
        'match_with' => '$0 alanına girilen değer $1 alanıyla aynı olmalıdır',
        'same_digit' => '$0 alanına girilen karekter uzunluğu $1 alanıyla eşit olmalıdır',
        'match_db' => '$0 veri tabanında bulunamadı',
        'match_db_with' => '$0 veri tabanında bulunamadı',
        'not_match_db' => '$0 Kaydı Zaten Mevcut'
    ];

    /**
     * @param null $datas
     * @return bool
     */
    public function validate(&$datas = null)
    {
        if ($datas !== null && is_array($datas)) {
            $this->setDatas($datas);
        } else {
            $this->datas = $this->getAttributes();
        }

        $rules = $this->getRules();
        $filters = $this->getFilters();

        $this->handleFilters($filters, $datas);
        $this->handleRules($rules);

        return !$this->failed();
    }

    /**
     * @param array $filters
     * @param array $datas
     */
    private function handleFilters(array $filters, &$datas)
    {
        foreach ($filters as $index => $subFilters) {
            $subFilters = explode("|", $subFilters);

            foreach ($subFilters as $subFilter) {
                $filterFunc = 'handleFilter' . ucfirst($subFilter);

                if (isset($this->datas[$index])) {
                    $filtred = call_user_func_array(array($this, $filterFunc), [$datas[$index]]);

                    $datas[$index] = $filtred;
                }
            }
        }
    }

    /**
     * @param $data
     * @return mixed|string
     */
    protected function handleFilterXss($data)
    {
        return $this->clean_input($data, 0);
    }

    private function clean_input($input, $safe_level = 0)
    {
        $output = $input;
        do {
            // Treat $input as buffer on each loop, faster than new var
            $input = $output;

            // Remove unwanted tags
            $output = $this->strip_tags($input);
            $output = $this->strip_encoded_entities($output);
            // Use 2nd input param if not empty or '0'
            if ($safe_level !== 0) {
                $output = $this->strip_base64($output);
            }
        } while ($output !== $input);

        return $output;
    }

    /*
     * Focuses on stripping encoded entities
     * *** This appears to be why people use this sample code. Unclear how well Kses does this ***
     *
     * @param   string  $input  Content to be cleaned. It MAY be modified in output
     * @return  string  $input  Modified $input string
     */
    private function strip_encoded_entities($input)
    {


        $input = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $input);
        $input = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $input);
        $input = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $input);
        $input = html_entity_decode($input, ENT_COMPAT, 'UTF-8');


        $input = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+[>\b]?#iu', '$1>', $input);


        $input = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $input);
        $input = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $input);
        $input = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $input);


        $input = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $input);
        $input = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $input);
        $input = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $input);
        return $input;
    }

    /*
     * Focuses on stripping unencoded HTML tags & namespaces
     *
     * @param   string  $input  Content to be cleaned. It MAY be modified in output
     * @return  string  $input  Modified $input string
     */
    private function strip_tags($input)
    {

        $input = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $input);

        $input = preg_replace('#</*\w+:\w[^>]*+>#i', '', $input);
        return $input;
    }

    /*
     * Focuses on stripping entities from Base64 encoded strings
     *
     * NOT ENABLED by default!
     * To enable 2nd param of clean_input() can be set to anything other than 0 or '0':
     * ie: xssClean->clean_input( $input_string, 1 )
     *
     * @param   string  $input      Maybe Base64 encoded string
     * @return  string  $output     Modified & re-encoded $input string
     */
    private function strip_base64($input)
    {
        $decoded = base64_decode($input);
        $decoded = $this->strip_tags($decoded);
        $decoded = $this->strip_encoded_entities($decoded);
        $output = base64_encode($decoded);
        return $output;
    }

    /**
     * @param $data
     * @return string
     */
    protected function handleFilterStrip_tags($data)
    {
        return strip_tags(htmlentities(htmlspecialchars($data)));
    }

    /**
     * @param array $rules
     */
    private function handleRules(array $rules)
    {
        foreach ($rules as $index => $value) {
            $ex = explode("|", $value);

            foreach ($ex as $item) {


                if (strpos($item, ":") !== false) {
                    $a = explode(":", $item);
                    $name = $a[0];

                    $args = explode(",", $a[1]);
                } else {
                    $name = $item;
                    $args = [];
                }

                $prepared = array_map(function ($value) {
                    return ucfirst($value);
                }, explode("_", $name));

                $func = "handleRule" . join('', $prepared);

                $return = call_user_func_array(array($this, $func), [$index, $args]);


                if ($name === 'required' && !$return) {
                    $this->errors[$name . '.' . $index] = $this->prepareErrorMessage($index, $this->messages[$name], $args);
                    break;
                }

                $this->handleRuleResult($return, $name, $index, $args);
            }
        }
    }

    /**
     * @param $return
     * @param $rule
     * @param $index
     */
    protected function handleRuleResult($return, $rule, $index, $args)
    {

        if (!$return) {
            $full = $rule . '.' . $index;

            $message = isset($this->messages[$full]) ? $this->messages[$full] : $this->messages[$rule];

            $this->errors[$full] = $this->prepareErrorMessage($index, $message, $args);
        }
    }

    /**
     * @param $field
     * @param $message
     * @param $args
     * @return mixed
     */
    private function prepareErrorMessage($field, $message, $args)
    {

        $args = array_merge([$field], $args);
        foreach ($args as $index => $arg) {
            $message = str_replace('$' . $index, $arg, $message);
        }

        return $message;
    }

    /**
     * @param $index
     * @return bool
     */
    protected function handleRuleRequired($index)
    {
        return (!empty($this->datas[$index]));
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    public function handleRuleMatchDb($index, $params = [])
    {
        if ($count = count($params) == 2) {
            $table = $params[0];
            $column = $params[1];
        } elseif ($count === 1) {
            $table = $params[0];
            $column = $index;
        } else {
            $table = $this->getTable();
            $column = $index;
        }

        $builder = QueryBuilder::createNewInstance($table)->where($column, $this->datas[$index]);

        return $builder->exists();
    }

    public function handleRuleMatchDbWith($index, $params = [])
    {
        if (isset($params[0])) {
            $other = $params[0];
        } else {
            return false;
        }

        $count = count($params);

        if ($count == 3) {
            $table = $params[1];
            $column = $params[2];
        } elseif ($count === 2) {
            $table = $params[1];
            $column = $index;
        } elseif ($count == 1) {
            $table = $this->getTable();
            $column = $index;
        }

        if (!isset($this->errors['match_db.' . $other])) {
            return QueryBuilder::createNewInstance($table)->where($column, $this->datas[$index])->exists();
        } else {
            return true;
        }
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    public function handleRuleNotMatchDb($index, $params = [])
    {
        if ($count = count($params) == 2) {
            $table = $params[0];
            $column = $params[1];
        } elseif ($count === 1) {
            $table = $params[0];
            $column = $index;
        } else {
            $table = $this->getTable();
            $column = $index;
        }

        $builder = QueryBuilder::createNewInstance($table)->where($column, $this->datas[$index]);

        return !$builder->exists();
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleMin($index, $params = [])
    {
        $min = $params[0];
        $data = $this->datas[$index];

        return ($data >= $min);
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleDigitMin($index, $params = [])
    {
        $min = isset($params[0]) ? $params[0] : false;

        if (false === $min) {
            return false;
        }
        $data = $this->datas[$index];

        return (strlen($data) >= $min);
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleMax($index, $params = [])
    {
        $max = $params[0];
        $data = $this->datas[$index];

        return ($data < $max);
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleDigitMax($index, $params = [])
    {
        $max = $params[0];
        $data = $this->datas[$index];

        return (strlen($data) < $max);
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleDigitBetween($index, $params = [])
    {
        $min = $params[0];
        $max = $params[1];
        $data = $this->datas[$index];

        return (strlen($data) >= $min && strlen($data) < $max);
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleBetween($index, $params = [])
    {
        $min = $params[0];
        $max = $params[1];
        $data = $this->datas[$index];

        return ($data >= $min && $data < $max);
    }

    /**
     * @param $index
     * @return bool
     */
    public function handleRuleNumeric($index)
    {
        return (is_numeric($this->datas[$index]));
    }

    /**
     * @param $index
     * @return bool
     */
    public function handleRuleAlpha($index)
    {
        return (preg_match("#^[a-zA-ZÀ-ÿ]+$#", $this->datas[$index]) === 1);
    }

    /**
     * @param $index
     * @return mixed
     */
    public function handleRuleUrl($index)
    {
        return filter_var($this->datas[$index], FILTER_VALIDATE_URL);
    }

    /**
     * @param $index
     * @return mixed
     */
    public function handleRuleEmail($index)
    {
        return filter_var($this->datas[$index], FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $index
     * @return bool
     */
    public function handleRuleAlphaNumeric($index)
    {
        return (preg_match("#^[a-zA-ZÀ-ÿ0-9]+$#", $this->datas[$index]) === 1);
    }

    /**
     * @param $index
     * @param $args
     * @return bool
     */
    public function handleRuleMatchWith($index, $args)
    {
        $target = $args[0];

        return ($this->datas[$index] === $this->datas[$target]);
    }

    /**
     * @param $index
     * @param $args
     * @return bool
     */
    public function handleRuleSameDigit($index, $args)
    {
        $target = $args[0];

        return (strlen($this->datas[$index]) === strlen($this->datas[$target]));
    }

    /**
     * @param $index
     * @return mixed
     */
    public function ip($index)
    {
        return filter_var($this->datas[$index], FILTER_VALIDATE_IP);
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
     * @return Model
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $filters
     * @return Model
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     * @return Model
     */
    public function setMessages($messages)
    {
        if (count($messages) !== 0) {
            $this->messages = array_merge($this->messages, $messages);
        }


        return $this;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return !empty($this->errors);
    }

    /**
     * @return array
     */
    public function failings()
    {
        return $this->errors;
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