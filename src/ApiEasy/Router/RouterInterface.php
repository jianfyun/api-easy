<?php
namespace ApiEasy\Router;

/**
 * RouterInterface
 *
 * @package ApiEasy\Router
 */
interface RouterInterface
{
    /**
     * Return an instance with the provided callback replacing the specific method and path.
     *
     * @param  string        $method   HTTP method
     * @param  string        $path     URI path
     * @param  array|Closure $callback Callback
     * @access public
     * @return self
     */
    public function withRule($method, $path, $callback);

    /**
     * Find the matched callback for the provided method and path.
     *
     * @param  string $method HTTP method
     * @param  string $path   URI path
     * @access public
     * @return array  Callback and the URI params.
     */
    public function route($method, $path);
}
