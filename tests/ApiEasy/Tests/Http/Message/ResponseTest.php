<?php
namespace ApiEasy\Tests\Http\Message;

use ApiEasy\Http\Message\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testJson()
    {
        $response = new Response();
        $value    = ['name' => 'Bob', 'age' => 26];
        $json     = json_encode($value);
        $response->withJsonBody($value);
        $this->assertEquals($json, (string) $response->getBody());
    }
}
