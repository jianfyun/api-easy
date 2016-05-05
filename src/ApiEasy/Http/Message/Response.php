<?php
namespace ApiEasy\Http\Message;

use PsrEasy\Http\Message\Response as PsrResponse;
use PsrEasy\Http\Message\Stream;

/**
 * Representation of an outgoing, server-side response.
 *
 * @uses    PsrEasy\Http\Message\Response
 * @package ApiEasy\Http\Message
 */
class Response extends PsrResponse
{
    /**
     * Return an instance with body in JSON format.
     *
     * @param  array|object $value The value to be converted to JSON;
     * @access public
     * @return self
     * @throw  \UnexpectedValueException if JSON encode fails.
     */
    public function withJson($value)
    {
        if (!is_array($value) || !is_object($value)) {
            throw new \InvalidArgumentException('The value must be array or object');
        }

        $json = json_encode($value);

        if ($json == false) {
            $message = 'JSON encode error: ' . json_last_error() . ', msg: ' . json_last_error_msg();
            throw new \UnexpectedValueException($message);
        }

        $this->withHeader('Content-Type', 'application/json');
        $stream = new Stream('php://memory', 'rw');
        $stream->write($json);
        $stream->rewind();
        $this->withBody($stream);
        return $this;
    }
}
