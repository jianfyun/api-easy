<?php
namespace ApiEasy\Tests\Router;

use ApiEasy\Router\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testRoute()
    {
        $router    = new Router();
        $callback1 = ['Test1', 'test'];
        $callback2 = ['Test2', 'test'];
        $router->withRule('GET', '/abc/test', $callback1);
        $router->withRule('POST', '/abc/test/{id}/{name}/list', $callback2);
        $handler1 = $router->route('GET', '/abc/test');
        $handler2 = $router->route('POST', '/abc/test/123/bob/list');
        $this->assertEquals($callback1, $handler1['callback']);
        $this->assertEquals($callback2, $handler2['callback']);
        $this->assertEquals('123', $handler2['params']['id']);
        $this->assertEquals('bob', $handler2['params']['name']);
    }
}
