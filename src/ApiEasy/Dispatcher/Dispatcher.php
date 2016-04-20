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
     * @return Response The HTTP Response instance.
     * @throw \BadFunctionCallException if the callback is not valid function.
     * @throw \BadMethodCallException if the callback is not valid method.
     * @throw \UnexpectedValueException if the return of the callback is not instance of Response.
     */
    public function dispatch($callback, Request $request, Response $response)
    {
        if (is_array($callback) && count($callback) == 2) {
            if (is_string($callback[0])) {
                $controller = new $callback[0]();
            }

            if (!$controller instanceof ControllerInterface) {
                throw new \BadMethodCallException('Controller is not instance of AbstractController');
            }

            if ($callback[1] == 'preDispatch' || $callback[1] == 'postDispatch') {
                throw new \BadMethodCallException('preDispatch or postDispatch can not be callback');
            }

            $response = call_user_func_array([$controller, 'preDispatch'], [$request, $response]);
            $response = call_user_func_array([$controller, $callback[1]], [$request, $response]);
            $response = call_user_func_array([$controller, 'postDispatch'], [$request, $response]);
        } elseif ($callback instanceof \Closure) {
            $response = call_user_func_array($callback, [$request, $response]);
        } else {
            throw new \BadFunctionCallException('Callback is neither Controller method nor Closure');
        }

        if (!$response instanceof Response) {
            throw new \UnexpectedValueException('Callback return is not instance of Response');
        }

        return $response;
    }
}
