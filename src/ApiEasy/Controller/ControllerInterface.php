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
     * @param Request $request HTTP request instance
     * @param Response $response HTTP response instance
     * @access public
     * @return void
     */
    public function preDispatch(Request $request, Response $response);

    /**
     * Execute some common operations after dispatching.
     * 
     * @param Request $request HTTP request instance
     * @param Response $response HTTP response instance
     * @access public
     * @return void
     */
    public function postDispatch(Request $request, Response $response);
}
