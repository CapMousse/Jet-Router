<?php

namespace Jet\Router;

class Router
{
    const PUT = 'PUT';
    const POST = 'POST';
    const GET = 'GET';
    const DELETE = 'DELETE';
    const CONSOLE = 'CONSOLE';

    /**
     * The current routes list
     * @var array
     */
    public $routes = array();

    /**
     * The routes list parsed to regexp
     * @var array
     */
    private $_parsed_routes = array();

    /**
     * The default route
     * @var array|function|boolean
     */
    private $_default = false;

    /**
     * Contain the 404 route
     * @var array|boolean
     */
    private $_error = false;
    
    /**
     * The list of authorized patterns in a route
     * @var array
     */
    private $_authorized_patterns = array(
        ':any'      => '.+',
        ':slug'     => '[a-zA-Z0-9\/_-]+',
        ':alpha'    => '[a-zA-Z]+',
        ':num'      => '[0-9]+'
    );

    /**
     * The current asked uri
     * @var string|boolean
     */
    private $_uri = false;

    /**
     * The request method
     * @var string|boolean
     */
    public static $method = false;

    /**
     * Create a new routing element
     * 
     * @param array $routes a route array
     * 
     * @throws \InvalidArgumentException
     * @return Router
     */
    function __construct($routes = array())
    {
        if (!is_array($routes)) {
            throw new \InvalidArgumentException("Routes must be an array");
        }

        $this->addRoutes($routes);
    }

    /**
     * Add routes
     * 
     * @param array $routes a route array
     * 
     * @throws \InvalidArgumentException
     * @return Boolean
     */
    public function addRoutes($routes)
    {
        if (!is_array($routes)) {
            throw new \InvalidArgumentException("Routes must be an array");
        }

        $this->routes = array_merge($this->routes, $routes);

        return true;    
    }

    /**
     * Launch the route parsing
     * 
     * @param string|null $route the toute to parse
     *
     * @throws \RuntimeException
     * @return mixed
     */
    public function launch($route = null){
        if (count($this->routes) === 0) {
            throw new \RuntimeException("No routes defined");
        }

        if (isset($this->routes['/'])) {
            $this->_default = $this->routes['/'];
            unset($this->routes['/']);   
        }
        
        if (isset($this->routes['error'])) {
            $this->_error = $this->routes['error'];
            unset($this->routes['error']);
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            self::$method = $_SERVER['REQUEST_METHOD'];
        } else {
            self::$method = "CONSOLE";
        }

        $this->parseRoutes();

        if ($route !== null) {
            $this->_uri = trim($route, '/');
        } else {
            $this->parseUri();
        }

        return $this->matchRoutes();
    }

    /**
     * parse current path to be used by the router
     *
     * @return  Array|null
     */
    private function parseUri(){
        // get current path
        if(isset($_SERVER['PATH_INFO'])){
            $path = $_SERVER['PATH_INFO'];
        }else if(isset($_SERVER['ORIG_PATH_INFO'])){
            $path = $_SERVER['ORIG_PATH_INFO'];
        }else{
            $path = @getenv('PATH_INFO');
        }

        // check if current path is not root url or core url, else return array of current route
        $this->_uri = (trim($path, '/') != '') ? trim($path, '/') : null;
    }

    /**
     * Parse all routes for uri matching
     * 
     * @return boolean
     */
    private function parseRoutes()
    {
        # transform routes into usable routes for the router
        # thanks to Taluu (Baptiste Clavié) for the help
        
        foreach ($this->routes as $key => $value) {
            $key = preg_replace('#\[([a-zA-Z0-9]+)\]:([a-z]+)#', '(?<$1>:$2)', rtrim($key, '/'));
            $key = str_replace(array_keys($this->_authorized_patterns), array_values($this->_authorized_patterns), $key);
            $this->_parsed_routes[$key] = $value;
        }

        return true;
    }

    /**
     * Try to match the current uri with all routes
     *
     * @throws \RuntimeException
     * @return mixed
     */
    private function matchRoutes(){
        if (($this->_uri == null || empty($this->_uri)) && $this->_default !== false) {
            return $this->launchRoute($this->_default);
        }
        
        if (isset($this->routes[$this->_uri])) {
            return $this->launchRoute($this->routes[$this->_uri]);
        }
        
        foreach ($this->_parsed_routes as $route => $val) {
            if (preg_match('#'.$route.'$#', $this->_uri, $array)) {

                $method_args = array();

                foreach ($array as $name => $value) {
                    if (!is_int($name)) {
                      $method_args[$name] = $value;  
                    } 
                }

                return $this->launchRoute($this->_parsed_routes[$route], $method_args);
            }
        }

        if ($this->_error !== false) {
           return $this->launchRoute($this->_error, array('url' => $this->_uri));
        }

        if ($this->_default !== false){
            return $this->launchRoute($this->_default);
        }

        throw new \RuntimeException('No route matched');
    }

    /**
     * Launch the asked route
     * 
     * @param array|function $route the function/class to call
     * @param array          $args  args of the route
     *
     * @throws \InvalidArgumentException
     * @return mixed
     */
    private function launchRoute($route, $args = array())
    {
        if (is_string($route)) {
            $route = explode(':', $route);
            $route[0] = new $route[0]();
        }
        
        return call_user_func_array($route, $args); 
    }

    /**
     * Get the method used
     * 
     * @static
     * @return  string
     */
    public static function getMethod()
    {
        return self::$method;
    }

    /**
     * Check if request is an XHR request
     * 
     * @static
     * @return  boolean
     */
    public static function isXHR()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}