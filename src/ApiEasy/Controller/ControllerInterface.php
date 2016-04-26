<?php
namespace ApiEasy\Controller;

use ApiEasy\Http\Message\Request;
use ApiEasy\Http\Message\Response;

/**
 * This interface defines the common methods for controllers.
 *
 * @package ApiEasy\Controller
 */
interface ControllerInterface
{
    /**
     * Execute some common operations before dispatching.
     *
     * @param  Request       $request  HTTP request instance
     * @param  Response      $response HTTP response instance
     * @access public
     * @return Response|null Return the HTTP response instance to skip the remaning processing.
     */
    public function preDispatch(Request $request, Response $response);

    /**
     * Execute some common operations after dispatching.
     *
     * @param  Request       $request  HTTP request instance
     * @param  Response      $response HTTP response instance
     * @access public
     * @return Response|null Return the HTTP response instance to skip the remaning processing.
     */
    public function postDispatch(Request $request, Response $response);

    /**
     * For each callback(action), the params and return are the same with preDispatch() and postDispatch()
     */
}
