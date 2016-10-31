<?php
namespace Sagi\Application;
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
    private $configs;

    /**
     * @var array
     */
    private $args;

    /**
     * @var string
     */
    private $file;


    /**
     * @var
     */
    private $dalvikPath;


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
     * @param array $configs
     * @throws Exception
     */
    public function __construct($configs = [], $file = '')
    {
        if (isset($configs['view_path']) && isset($configs['dalvik_path'])) {
            $this->setConfigs($configs);
            $this->checkDirs();
        } else {
            throw new Exception('we need to view_path and dalvik_path for make a good start');
        }

        $this->file = $file;
        $this->with('viewClassObject', $this);
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
     * @param $file
     */
    public function compileInc($file)
    {
        $content = $this->render($file, true);

        return $content;
    }

    /**
     * @return mixed|View
     */
    public function compileHeader()
    {
        return $this->compileInc('header');
    }

    /**
     * @return mixed|View
     */
    public function compileFooter()
    {
        return $this->compileInc('footer');
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
                $content = str_replace($match, '<?php _e(' . $matches[1][$i] . ', false); ?>', $content);
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
                $content = str_replace($match, '<?php _e(' . $matches[1][$i] . '); ?>', $content);
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
     * @param null $file
     */
    public function render($file = null, $return = false)
    {
        if (!is_null($file)) {
            $this->setFile($file);
        }

        $this->with('viewClassObject', $this);

        if ($content = $this->getFileContent()) {
            $replaceContent = $this->handleContent($content);

            if ($return) {
                return $replaceContent;
            } else {
                $this->putContentOnDalvik($replaceContent);
                return $this;
            }
        } else {
            throw new Exception($file . ' does not exists in your view_path');
        }
    }

    /**
     * @throws Exception
     */
    public
    function show()
    {
        $data = $this->getArgs();

        extract($data, EXTR_SKIP);

        if (!empty($this->dalvikPath)) {
            try {
                include $this->dalvikPath;
            } catch (Exception $e) {
                throw new Exception("Gösterme işlemi sırasında bir hata oluştu:, message:" . $e->getMessage());
            }
        }

    }

    /**
     * @param string $content
     */
    private
    function putContentOnDalvik($content)
    {
        $this->dalvikPath = $dalvikFile = $this->configs['dalvik_path'] . DIRECTORY_SEPARATOR . md5($this->getFile()) . ".php";


        if (!file_exists($dalvikFile)) {
            chmod($this->configs['dalvik_path'], 0777);
            touch($dalvikFile);
        }
        file_put_contents($dalvikFile, $content);

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
     * @return string
     */
    private
    function getFileContent()
    {
        if ($path = $this->findFile($this->getFile())) {
            return file_get_contents($path);
        } else {
            return false;
        }
    }

    /**
     * @param $file
     * @return string
     */
    private
    function findFile($file)
    {
        $fullpath = $this->configs['view_path'] . DIRECTORY_SEPARATOR . $file . ".blade.php";

        if (file_exists($fullpath)) {
            return $fullpath;
        }
    }

    /**
     * checks dirs exists
     */
    private
    function checkDirs()
    {
        if (!is_dir($this->configs['view_path'])) {
            mkdir($this->configs['view_path'], 0777);
        }

        if (!is_dir($this->configs['dalvik_path'])) {
            mkdir($this->configs['dalvik_path'], 0777);

        }
    }

    /**
     * @return Compiler
     */
    public
    function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * @param Compiler $compiler
     * @return View
     */
    public
    function setCompiler($compiler)
    {
        $this->compiler = $compiler;
        return $this;
    }


    /**
     * @return array
     */
    public
    function getConfigs()
    {
        return $this->configs;
    }

    /**
     * @param array $configs
     * @return View
     */
    public
    function setConfigs($configs)
    {
        $this->configs = $configs;
        return $this;
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


}

function _e($content, $cleanHtml = true)
{
    if ($cleanHtml) {
        echo htmlspecialchars(htmlentities(strip_tags($content)));
    } else {
        echo $content;
    }
}
