<?php
/**
 * Bu Dosya AnonymFramework'e ait bir dosyadır.
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 */

namespace Sagi\Http;
use Sagi\Database\CookieManager;


/**
 * the class of request
 *
 * Class Request
 * @package Anonym\Http
 */
class Request
{


    /**
     *  the constant of http head method
     */
    const METHOD_HEAD = 'HEAD';

    /**
     *  the constant of http get method
     */
    const METHOD_GET = 'GET';

    /**
     *  the constant of http post method
     */
    const METHOD_POST = 'POST';

    /**
     *  the constant of http put method
     */
    const METHOD_PUT = 'PUT';

    /**
     *  the constant of http patch method
     */
    const METHOD_PATCH = 'PATCH';

    /**
     *  the constant of http delete method
     */
    const METHOD_DELETE = 'DELETE';

    /**
     *  the constant of http purge method
     */
    const METHOD_PURGE = 'PURGE';

    /**
     *  the constant of http options method
     */
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     *  the constant of http trace method
     */
    const METHOD_TRACE = 'TRACE';

    /**
     *  the constant of http connect method
     */
    const METHOD_CONNECT = 'CONNECT';

    /**
     * the headers of request headers
     *
     * @var array
     */
    private $headers = [];

    /**
     * the reposity of cookies
     *
     * @var array
     */
    private $cookies = [];


    /**
     * the instance of server
     *
     * @var Server
     */
    private $server;

    /**
     * the reposity of request segments
     *
     * @var array
     */
    private $segments = [];

    /**
     * the instance of validation
     *
     *
     * @var Validate
     */
    private $validate;

    /**
     * the instance of input
     *
     * @var Input
     */
    private $input;

    /**
     * the instance of query
     *
     * @var Query
     */
    private $query;


    /**
     * Sınıfı başlatır ve header bilgilerini atar
     */
    public function __construct()
    {
        $headers = (new ServerHttpHeaders())->getHeaders();
        $this->setHeaders($headers);
        $this->setCookies(CookieManager::getCookies());
        $this->setServer(new Server());
        $this->setInput(new Input());
        $this->setQuery( new Query());
        $this->segments = explode('/', $this->getUrl());
    }
    /**
     * upload a file with input name and uplaod dir,
     *
     * ////////////////
     *
     *  $name parameter must be a string and it is has to be exists
     *  $uploadDir         must be an instance of string and it's must be a dir.
     *
     * @param string $name        the name of upload input
     * @param string $uploadDir   the dir to upload file
     * @throws FileNotUploadedException
     * @return FileUpload
     */
    public function file($name = '', $uploadDir = UPLOAD)
    {
        if (isset($_FILES[$name])) {
            $file = new FileUpload($_FILES[$name], $uploadDir);
            return $file;
        } else {
            throw new FileNotUploadedException(sprintf('Your %s file is not uploaded yet', $name));
        }
    }


    /**
     * find the user client ip, this can be find some proxies
     *
     * @return string
     */
    public function ip()
    {
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
            if (strstr($ip, ',')) {
                $tmp = explode(',', $ip);
                $ip = trim($tmp[0]);
            }
        } else {
            $ip = getenv("REMOTE_ADDR");
        }

        return $ip;
    }


    /**
     * add a new header to response
     *
     * @param string $name the name of header
     * @param string $value the value of header
     * @return $this
     */
    public function header($name, $value = '')
    {
        $this->getResponse()->header($name, $value);
        return $this;
    }

    /**
     * return the current request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * if some one called this method, firstly we gonna determine index.php is located on root path
     * and if it is located on root, we gonna server request uri parameter
     * if it is not, we gonna return $_SERVER[PATH_INFO]
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->findDocumentRootInScriptFileName() === $this->removeLastSlash($this->root)) {
            return $this->getRequestUri();
        }
        $path = $this->server->get('PATH_INFO');

        return $path;
    }

    /**
     * find and return user called uri
     *
     * @return string
     */
    public function getUri(){
        return $this->getUrl();
    }
    /**
     * remove last slash from your string
     *
     * @param string $string
     * @return string
     */
    protected function removeLastSlash($string)
    {
        if (substr($string, -1) === '/') {
            return substr($string, 0, strlen($string) - 1);
        }

        return $string;
    }

    /**
     * find document root in server script filename
     *
     * @return bool|string
     */
    private function findDocumentRootInScriptFileName()
    {

        $filename = $this->server->get('SCRIPT_FILENAME') ?: false;
        if (false === $filename) {
            return false;
        }
        $parse = explode('/', $filename);
        $count = count($parse);
        if ($count && $count > 1) {
            $path = array_slice($parse, 0, $count-1);
            return $this->removeLastSlash(join('/', $path));
        } else {
            return '/';
        }

    }

    /**
     * find and return requested port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * find and return requested host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }


    /**
     * find and return request scheme, if we can not find scheme we gonna return 'http'
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme ?: 'http';
    }


    /**
     * find and return http_referer parameter
     *
     * @return string
     */
    public function back()
    {
        return $this->referer;
    }

    /**
     * find and create full host uri with http scheme and port
     *
     * example : localhost
     * example : localhot:443
     *
     * @return string
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if ('http' === $scheme && $port === '80' || 'https' === $scheme && $port === '443') {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    /**
     * find and return http query string
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->server->get('QUERY_STRING');
    }

    /**
     * find and return requested uri
     *
     *
     * @return string
     */
    public function getRequestUri()
    {
        return str_replace('?'.$this->getQueryString(), '', $this->uri);
    }

    /**
     * find the scheme and request uri
     *
     * @return string
     */
    public function getBaseUri()
    {
        if ('' !== $qs = $this->getQueryString()) {
            $qs = '?' . $qs;
        }

        return $this->getSchemeAndHost() . $this->getRequestUri() . $qs;
    }

    /**
     * find base url without query string
     *
     * @return string
     */
    public function getBaseWithoutQuery()
    {
        return $this->getSchemeAndHost() . $this->getRequestUri();
    }

    /**
     * we gonna determine url availability and return it
     *
     * @param string $url
     * @return bool
     */
    public function isAvailableUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }


    /**
     * return all input variables
     *
     * @return mixed
     */
    public function all()
    {
        return $this->getInput()->getAll();
    }

    /**
     * return all query variables
     *
     * @return mixed
     */
    public function query(){
        return  $this->getQuery()->getAll();
    }

    /**
     * set the default php locale
     *
     * @param string $locale
     * @return $this
     */
    public function setDefaultLocale($locale)
    {
        try {
            if (class_exists('Locale', false)) {
                \Locale::setDefault($locale);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * get the scheme and host url
     *
     * @return string
     */
    public function getSchemeAndHost()
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * get url segment
     *
     * @param int $segment
     * @return string|bool
     */
    public function segment($segment)
    {
        $segments = $this->segments;

        return isset($segments[$segment - 1]) ? $segments[$segment - 1] : false;
    }

    /**
     * get the server variable with references or directly
     *
     * @param string $name the name of variable
     * @throws ServerVariableException
     * @return string
     */
    public function __get($name)
    {
        return $this->server->get($name);
    }

    /**
     * generate an json string
     *
     * @param mixed $content
     * @return string
     */
    public function json($content = null)
    {
        return json_encode($content);
    }

    /**
     * check request, if made with ajax return true
     *
     * @return bool
     */
    public function isAjax()
    {
        return strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest' ? true : false;
    }

    /**
     * determine http request method
     *
     * @return bool if method is post return true, else return false
     */
    public function isPost(){
        return $this->getMethod() === self::METHOD_POST;
    }

    /**
     * determine http request method
     *
     * @return bool if method is get return true, else return false
     */
    public function isGet(){
        return $this->getMethod() === self::METHOD_GET || $this->getMethod() === self::METHOD_HEAD;
    }

    /**
     * determine http request method
     *
     * @return bool if method is put return true, else return false
     */
    public function isPut(){
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * determine http request method
     *
     * @return bool if method is delete return true, else return false
     */
    public function isDelete(){
        return $this->getMethod() === self::METHOD_DELETE;
    }


    /**
     * determine http request method
     *
     * @return bool if method is options return true, else return false
     */
    public function isOptions(){
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

    /**
     * determine http request method
     *
     * @return bool if method is options return true, else return false
     */
    public function isPurge(){
        return $this->getMethod() === self::METHOD_PURGE;
    }


    /**
     * determine http request method
     *
     * @return bool if method is options return true, else return false
     */
    public function isTrace(){
        return $this->getMethod() === self::METHOD_TRACE;
    }

    /**
     * determine http request method
     *
     * @return bool if method is options return true, else return false
     */
    public function isConnect(){
        return $this->getMethod() === self::METHOD_CONNECT;
    }

    /**
     * determine http request method
     *
     * @return bool if method is options return true, else return false
     */
    public function isPatch(){
        return $this->getMethod() === self::METHOD_PATCH;
    }
    /**
     * Get a subset of the items from the input data.
     *
     * @param array $keys
     * @return array
     */
    public function only($keys = [])
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $all = $this->all();

        $result = [];

        // now we will register keys to result variable,
        // each of variables must be exists, if they are not exists
        // null will be add to result
        foreach ($keys as $key) {
            $result[$key] = isset($all[$key]) ? $all[$key] : null;
        }

        return $result;

    }


    /**
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param array $segments
     * @return Request
     */
    public function setSegments($segments)
    {
        $this->segments = $segments;
        return $this;
    }

    /**
     * @return Validate
     */
    public function getValidate()
    {
        return $this->validate;
    }

    /**
     * @param Validate $validate
     * @return Request
     */
    public function setValidate(Validate $validate = null)
    {
        $this->validate = $validate;
        return $this;
    }


    /**
     * @return \Sagi\Http\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * register the server instance
     *
     * @param \Sagi\Http\Server $server
     * @return Request
     */
    public function setServer(Server $server)
    {
        $this->server = $server;
        return $this;
    }


    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @param array $cookies
     * @return Request
     */
    public function setCookies($cookies)
    {
        $this->cookies = $cookies;

        return $this;
    }


    /**
     * Header bilgilerini döndürür
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Header bilgilerini atar
     *
     * @param array $headers
     * @return Request
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }


    /**
     * return the registered response instance
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * register the response instance
     *
     * @param Response $response the instance of response
     * @return Request return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Input
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param Input $input
     * @return Request
     */
    public function setInput(Input $input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param Query $query
     * @return Request
     */
    public function setQuery(Query $query)
    {
        $this->query = $query;
        return $this;
    }

}
