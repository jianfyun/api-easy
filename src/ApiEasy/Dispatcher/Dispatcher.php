<?php
namespace ApiEasy\Dispatcher;

use ApiEasy\Controller\ControllerInterface;
use ApiEasy\Http\Message\Request;
use ApiEasy\Http\Message\Response;

/**
 * Representation of a callback dispatcher.
 *
 * @uses DispatcherInterface
 * @package
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * Check the callback, and convert it to the normalized form.
     *
     * @param  mixed  $callback  Callback.
     * @param  string $namespace Namespace for the callback.
     * @access public
     * @return mixed  The normalized callback.
     * @throw \InvalidArgumentException if the callback is invalid.
     */
    public function normalize($callback, $namespace = '')
    {
        if (is_array($callback) && count($callback) == 2) {
            if (is_string($callback[0])) {
                $callback[0] = $namespace . '\\' . $callback[0];
            }
        }

        return $callback;
    }

    /**
     * Dispatch the callback for the matched route.
     *
     * @param  array    $callback Callback.
     * @param  Request  $request  The HTTP Request instance.
     * @param  Response $response The HTTP Response instance.
     * @access protected
     * @return void
     * @throw \BadFunctionCallException if the callback is not valid function.
     * @throw \BadMethodCallException if the callback is not valid method.
     */
    public function dispatch($callback, Request $request, Response $response)
    {
        if (is_array($callback) && count($callback) == 2) {
            if (is_string($callback[0])) {
                $className  = $callback[0];
                var_dump($className);exit;
                $controller = new $className();
            }

            if (!$controller instanceof ControllerInterface) {
                throw new \BadMethodCallException('Controller is not instance of AbstractController');
            }

            if ($callback[1] == 'preDispatch' || $callback[1] == 'postDispatch') {
                throw new \BadMethodCallException('preDispatch or postDispatch can not be callback');
            }

            $this->execute([$controller, 'preDispatch'], $request, $response);
            $this->execute([$controller, $callback[1]], $request, $response);
            $this->execute([$controller, 'postDispatch'], $request, $response);
        } elseif ($callback instanceof \Closure) {
            $response = call_user_func_array($callback, [$request, $response]);
        } else {
            throw new \BadFunctionCallException('Callback is neither Controller method nor Closure');
        }
    }

    /**
     * Execute thie provided method of Controller instance.
     *
     * @param  array    $callback The method of Controller instance.
     * @param  Request  $request  The HTTP Request instance.
     * @param  Response $response The HTTP Response instance.
     * @access protected
     * @return void
     * @throw \UnexpectedValueException if the return of the callback is not instance of Response.
     */
    protected function execute(array $callback, Request $request, Response $response)
    {
        call_user_func_array($callback, [$request, $response]);

        if (!$request instanceof Request || !$response instanceof Response) {
            $message = get_class($callback[0]) . "::{$callback[1]} is not allowed to modify Request or Response type";
            throw new \UnexpectedValueException($message);
        }
    }
}
