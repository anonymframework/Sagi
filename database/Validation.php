<?php
namespace Sagi\Database;

trait Validation
{

    /**
     * @var array
     */
    protected $rules;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var array
     */
    protected $filtredDatas;

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
        'match_with' => '$0 alanına girilen değer $1 alanıyla uygun olmalıdır',
        'same_digit' => '$0 alanına girilen karekter uzunluğu $1 alanıyla eşit olmalıdır'
    ];


    /**
     *
     */
    public function validate()
    {
        $rules = $this->getRules();
        $filters = $this->getFilters();

        $this->handleFilters($filters);
        $this->handleRules($rules);

        return !$this->failed();
    }

    /**
     * @param array $filters
     */
    private function handleFilters(array $filters)
    {
        foreach ($filters as $index => $subFilters) {
            $subFilters = explode("|", $subFilters);

            foreach ($subFilters as $subFilter) {
                $filterFunc = 'handleFilter' . ucfirst($subFilter);

                if ($this->hasAttribute($index)) {
                    $filtred = call_user_func_array(array($this, $filterFunc), [$this->attribute($index)]);
                    $this->attributes[$index] = $filtred;
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
        // Fix &entity\n;
        $input = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $input);
        $input = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $input);
        $input = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $input);
        $input = html_entity_decode($input, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $input = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+[>\b]?#iu', '$1>', $input);
        // Remove javascript: and vbscript: protocols
        $input = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $input);
        $input = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $input);
        $input = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $input);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
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
        // Remove tags
        $input = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $input);
        // Remove namespaced elements
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

                $func = "handleRule" . ucfirst($name);

                $return = call_user_func_array(array($this, $func), [$index, $args]);

                if ($name === 'required' && !$return) {
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
            $this->errors[$rule . '.' . $index] = $this->prepareErrorMessage($index, $this->messages[$rule], $args);
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
        return ($this->hasAttribute($index) && !empty($this->attributes[$index]));
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleMin($index, $params = [])
    {
        $min = $params[0];
        $data = $this->attributes[$index];

        return ($data >= $min);
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleDigit_min($index, $params = [])
    {
        $min = $params[0];
        $data = $this->attributes[$index];

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
        $data = $this->attributes[$index];

        return ($data < $max);
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleDigit_max($index, $params = [])
    {
        $max = $params[0];
        $data = $this->attributes[$index];

        return (strlen($data) < $max);
    }

    /**
     * @param $index
     * @param array $params
     * @return bool
     */
    protected function handleRuleDigit_between($index, $params = [])
    {
        $min = $params[0];
        $max = $params[1];
        $data = $this->attributes[$index];

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
        $data = $this->attributes[$index];

        return ($data >= $min && $data < $max);
    }

    /**
     * @param $index
     * @return bool
     */
    public function handleRuleNumeric($index)
    {
        return (is_numeric($this->attributes[$index]));
    }

    /**
     * @param $index
     * @return bool
     */
    public function handleRuleAlpha($index)
    {
        return (preg_match("#^[a-zA-ZÀ-ÿ]+$#", $this->attributes[$index]) === 1);
    }

    /**
     * @param $index
     * @return mixed
     */
    public function handleRuleUrl($index)
    {
        return filter_var($this->attributes[$index], FILTER_VALIDATE_URL);
    }

    /**
     * @param $index
     * @return mixed
     */
    public function handleRuleEmail($index)
    {
        return filter_var($this->attributes[$index], FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $index
     * @return bool
     */
    public function handleRuleAlpha_numeric($index)
    {
        return (preg_match("#^[a-zA-ZÀ-ÿ0-9]+$#", $this->attributes[$index]) === 1);
    }

    /**
     * @param $index
     * @param $args
     * @return bool
     */
    public function handleRuleMatch_with($index, $args)
    {
        $target = $args[0];

        return ($this->attributes[$index] === $this->attributes[$target]);
    }

    /**
     * @param $index
     * @param $args
     * @return bool
     */
    public function handleRuleSame_digit($index, $args)
    {
        $target = $args[0];

        return (strlen($this->attributes[$index]) === strlen($this->attributes[$target]));
    }

    /**
     * @param $index
     * @return mixed
     */
    public function ip($index)
    {
        return filter_var($this->attributes[$index], FILTER_VALIDATE_IP);
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
            $this->messages = $messages;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        return (count($this->errors) > 0);
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

}