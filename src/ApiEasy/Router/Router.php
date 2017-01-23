<?php
namespace ApiEasy\Router;

/**
 * Representation of an URI router.
 *
 * @uses    RouterInterface
 * @package ApiEasy\Router
 */
class Router implements RouterInterface
{
    /**
     * Exact route rules.
     *
     * @var array
     * @access protected
     */
    protected $exact = [];

    /**
     * Fuzzy route rules.
     *
     * @var array
     * @access protected
     */
    protected $fuzzy = [];

    /**
     * Asterisk route rules.
     *
     * @var array
     * @access protected
     */
    protected $asterisk = [];

    /**
     * __construct
     *
     * @access public
     */
    public function __construct()
    {
        $this->exact    = [];
        $this->fuzzy    = [];
        $this->asterisk = [];
    }

    /**
     * Return an instance with the provided callback replacing the specific method and path.
     *
     * @param  string        $method   HTTP method
     * @param  string        $path     URI path
     * @param  array|Closure $callback Callback
     * @access public
     * @return self
     */
    public function withRule($method, $path, $callback)
    {
        if ($path == '*') {
            $this->asterisk[$method] = $callback;
            return $this;
        }

        $varPos = strpos($path, '{');

        if (false === $varPos) {
            $this->exact[$method][$path] = $callback;
            return $this;
        }

        $prefix  = substr($path, 0, $varPos);
        $pattern = str_replace('{', '(?P<', $path);
        $pattern = str_replace('}', '>[^/]+?)', $pattern);
       
        $this->fuzzy[$method][$prefix][$pattern] = $callback;
        return $this;
    }

    /**
     * Find the matched callback for the provided method and path.
     *
     * @param  string $method HTTP method
     * @param  string $path   URI path
     * @access public
     * @return array  Callback and the URI params.
     */
    public function route($method, $path)
    {
        $match = ['callback' => null, 'params' => []];

        if (is_array($this->exact[$method])) {
            if (isset($this->exact[$method][$path])) {
                $match['callback'] = $this->exact[$method][$path];
                return $match;
            }
        }

        if (is_array($this->fuzzy[$method])) {
            foreach ($this->fuzzy[$method] as $prefix => $rules) {
                if (0 !== strpos($path, $prefix)) {
                    continue;
                }

                $match = $this->matchFuzzy($path, $rules);
            }
        }

        if ($match['callback'] == null && isset($this->asterisk[$method])) {
            $match['callback'] = $this->asterisk[$method];
            return $match;
        }

        return $match;
    }

    /**
     * Find the callback according to the fuzzy rules.
     *
     * @param  string $path  URI path
     * @param  array  $rules Fuzzy route rules
     * @access protected
     * @return array  Callback and the URI params.
     */
    protected function matchFuzzy($path, array $rules)
    {
        $match = ['callback' => null, 'params' => []];

        foreach ($rules as $pattern => $callback) {
            $params   = [];
            $captures = [];
            $varNum   = preg_match_all("#^{$pattern}$#", $path, $captures);

            if (0 == $varNum) {
                continue;
            }

            foreach ($captures as $name => $values) {
                if (is_string($name)) {
                    $params[$name] = urldecode($values[0]);
                }
            }

            $match['callback'] = $callback;
            $match['params']   = $params;
        }

        return $match;
    }
}
