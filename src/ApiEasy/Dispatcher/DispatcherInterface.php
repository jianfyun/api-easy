<?php
namespace ApiEasy\Dispatcher;

use ApiEasy\Http\Message\Request;
use ApiEasy\Http\Message\Response;

/**
 * This interface defines the common methods for dispatchers.
 *
 * @package ApiEasy\Dispatcher
 */
interface DispatcherInterface
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
    public function normalize($callback, $namespace = '');

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
    public function dispatch($callback, Request $request, Response $response);
}
