<?php
namespace Sagi\Database;

use Exception;

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 26.07.2016
 * Time: 21:53
 */
class View
{

    /**
     * @var array
     */
    private $args = [];

    /**
     * @var string
     */
    private $file;


    /**
     * @var array
     */

    private $rawTags = ["{!!", "!!}"];
    /**
     * @var array
     */
    private $contentTags = ["{{", "}}"];

    /**
     * @var array
     */
    private $endTags = [
        'endif',
        'endwhile',
        'endfor',
        'endforeach',
        'break'
    ];

    /**
     * @var string
     */
    public static $templatePath = 'templates';

    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $loopTags = [
        'if',
        'while',
        'for',
        'foreach',
        'swith',
        'case',
        'else',
        'elseif'
    ];

    private $selfMethods = [
        'inc',
        'header',
        'footer'
    ];

    /**
     * View constructor.
     * @param string $file
     * @param array|null $datas
     */
    public function __construct($file, $datas = null)
    {
        $this->with('this', $this);

        if ($datas !== null) {
            $this->with($datas);
        }

        $this->content = static::createContentWithFile($file);
    }


    /**
     * @param $file
     * @param $datas
     * @return static
     */
    public static function createInstance($file, $datas)
    {
        return new static($file, $datas);
    }

    /**
     * @param $content
     * @return mixed
     */
    private function compile($content)
    {
        $parsed = explode("\n", $content);

        $newContent = '';
        foreach ($parsed as $line) {
            $lineContent = $this->compileTags($line);
            $newContent .= $lineContent . "\n";
        }

        return $newContent;
    }

    private function compileTags($content)
    {
        $contentPattern = "/" . $this->contentTags[0] . "(.*?)" . $this->contentTags[1] . "/s";
        $rawPattern = "/" . $this->rawTags[0] . "(.*?)" . $this->rawTags[1] . "/s";


        $content = $this->compileOpenedTags($content);
        $content = $this->compileContentEchos($content, $contentPattern);
        $content = $this->compileRawEchos($content, $rawPattern);

        return $content;
    }

    private function compileOpenedTags($content)
    {
        if (preg_match("/@(.*)/s", $content, $matches)) {

            $match = $matches[1];
            preg_match('/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $matches[0], $m);
            $raw = $m[1];

            if (array_search($raw, $this->loopTags) !== false) {
                $content = str_replace($matches[0], "<?php " . $match . ": ?>", $content);
            } elseif (array_search($raw, $this->endTags) !== false) {
                $content = str_replace($matches[0], "<?php " . $match . "; ?>", $content);
            } elseif (array_search($raw, $this->selfMethods) !== false) {
                $replace = $this->procressMethod($raw, $m);
                $content = str_replace($matches[0], $replace, $content);
            } else {
                $content = str_replace($matches[0], "<?php " . $match . "; ?>", $content);
            }


        }


        return $content;
    }


    /**
     * @param $raw
     * @param $m
     * @return string
     */
    private function procressMethod($raw, $m)
    {
        $methodName = 'compile' . ucfirst($raw);
        $argsN = [];
        $args = explode(',', isset($m[4]) ? $m[4] : '');

        foreach ($args as $item) {
            $argsN[] = str_replace(["'", '"'], "", $item);
        }

        return call_user_func_array(array($this, $methodName), $argsN);
    }


    /**
     * @param $content
     * @param $rawPattern
     * @return string
     */
    private
    function compileRawEchos($content, $rawPattern)
    {
        if (preg_match_all($rawPattern, $content, $matches)) {

            for ($i = 0; $i < count($matches[0]); $i++) {
                $match = $matches[0][$i];
                $content = str_replace($match, '<?php $this->_e(' . $matches[1][$i] . ', false); ?>', $content);
            }
        }

        return $content;
    }

    /**
     * @param $content
     * @param $contentPattern
     * @return mixed
     */
    private
    function compileContentEchos($content, $contentPattern)
    {
        if (preg_match_all($contentPattern, $content, $matches)) {
            for ($i = 0; $i < count($matches[0]);
                 $i++) {
                $match = $matches[0][$i];
                $content = str_replace($match, '<?php $this->_e(' . $matches[1][$i] . '); ?>', $content);
            }
        }

        return $content;
    }


    /**
     * @param mixed $a
     * @param null $b
     * @return $this
     */
    public
    function with($a, $b = null)
    {
        if (is_null($b)) {
            $this->args = array_merge($this->args, $a);
        } else {
            $this->args[$a] = $b;
        }

        return $this;
    }


    /**
     * @return string
     * @throws Exception
     */
    public function render()
    {

        if ($content = $this->getContent()) {

            $replaceContent = $this->handleContent($content);
            return $replaceContent;

        } else {
            throw new Exception('Your content is empty');
        }
    }

    /**
     * @throws Exception
     */
    public function show()
    {
        $data = $this->getArgs();

        extract($data, EXTR_SKIP);

        ob_start();
        eval(' ?> ' . $this->render() . '<?php ');
        return ob_get_clean();
    }


    /**
     * @param $content
     * @return mixed
     */
    private
    function handleContent($content)
    {
        return $this->compile($content);
    }


    /**
     * @return View
     */
    public static function createContentWithFile($file)
    {

        if ($path = static::findFile($file)) {
            $content = file_get_contents($path);

            return $content;
        } else {
            return false;
        }
    }

    /**
     * @param $file
     * @return string
     */
    public static function findFile($file)
    {
        $fullpath = static::$templatePath . DIRECTORY_SEPARATOR . $file . ".temp";

        if (file_exists($fullpath)) {
            return $fullpath;
        }
    }


    /**
     * @return string
     */
    public
    function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return $this
     */
    public
    function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return array
     */
    public
    function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return View
     */
    public
    function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return View
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function _e($content, $cleanHtml = true)
    {
        if ($cleanHtml) {
            echo htmlspecialchars(htmlentities(strip_tags($content)));
        } else {
            echo $content;
        }
    }
}
