<?php
namespace ApiEasy\Http\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use PsrEasy\Http\Message\Response;
use PsrEasy\Http\Message\Stream;

/**
 * Representation of an HTTP client.
 *
 * @package ApiEasy\Http\Client
 */
class Client
{
    /**
     * The curl handler.
     *
     * @var resource
     * @access protected
     */
    protected $curl = null;

    /**
     * Information regarding a specific transfer.
     *
     * @var array
     * @access protected
     */
    protected $info = [];

    /**
     * The seconds before requesting timeout.
     *
     * @var int
     * @access protected
     */
    protected $timeout = 3;

    /**
     * Retrieves the timeout seconds.
     *
     * @return int
     * @access public
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Return an instance with the provided timeout seconds.
     *
     * @param  int $seconds The timeout seconds of HTTP request.
     * @access public
     * @return self
     */
    public function withTimeout($seconds)
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Send HTTP request and return the response.
     *
     * @param RequestInterface $request The HTTP Request instance.
     * @return ResponseInterface The HTTP Response instance.
     */
    public function send(RequestInterface $request)
    {
        $this->curl = curl_init();

        $this->setVersion($request);
        $this->setMethod($request);
        $this->setUri($request);
        $this->setHeaders($request);
        $this->setBody($request);
        $this->setOthers();

        $result = curl_exec($this->curl);

        $this->info = curl_getinfo($this->curl);

        if ($result === false) {
            throw new \RuntimeException('Execute HTTP request error: ' . curl_error(), curl_errno());
        }

        return $this->buildResponse($request);
    }

    /**
     * Set the version of HTTP request for CURL.
     *
     * @param RequestInterface $request The HTTP request instance.
     */
    private function setVersion(RequestInterface $request)
    {
        $version = $request->getProtocolVersion();
        $value = CURL_HTTP_VERSION_NONE;

        if ($version == '1.1') {
            $value = CURL_HTTP_VERSION_1_1;
        } elseif ($version == '1.0') {
            $value = CURL_HTTP_VERSION_1_0;
        }

        if (!curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $value)) {
            throw new \RuntimeException('Set HTTP version error: ' . curl_error(), curl_errno());
        }
    }

    /**
     * Set the method of HTTP request for CURL.
     *
     * @param RequestInterface $request The HTTP request instance.
     * @throw \UnexpectedValueException if the method is empty.
     * @throw \RuntimeException if set option for CURL fails.
     */
    private function setMethod(RequestInterface $request)
    {
        $method = $request->getMethod();

        if ('' == $method) {
            throw new \UnexpectedValueException('The method of request is empty');
        }

        if (!curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method)) {
            throw new \RuntimeException('Set HTTP method error: ' . curl_error(), curl_errno());
        }
    }

    /**
     * Set the URI of HTTP request for CURL.
     *
     * @param RequestInterface $request The HTTP request instance.
     * @throw \UnexpectedValueException if the uri is not instance of UriInterface.
     * @throw \RuntimeException if set option for CURL fails.
     */
    private function setUri(RequestInterface $request)
    {
        $uri = $request->getUri();

        if (!$uri instanceof UriInterface) {
            throw new \UnexpectedValueException('The URI of request is not instance of UriInterface');
        }

        if (!curl_setopt($this->curl, CURLOPT_URL, (string)$uri)) {
            throw new \RuntimeException('Set HTTP URL error: ' . curl_error(), curl_errno());
        }
    }

    /**
     * Set the headers of HTTP request for CURL.
     *
     * @param RequestInterface $request The HTTP request instance.
     * @throw \RuntimeException if set option for CURL fails.
     */
    private function setHeaders(RequestInterface $request)
    {
        $headers = [];

        foreach ($request->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $headers[] = sprintf('%s: %s', $name, $value);
            }
        }

        if (!curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers)) {
            throw new \RuntimeException('Set HTTP headers error: ' . curl_error(), curl_errno());
        }
    }

    /**
     * Set the body of HTTP request for CURL.
     *
     * @param RequestInterface $request The HTTP request instance.
     * @throw \UnexpectedValueException if the body is not instance of StreamInterface.
     * @throw \RuntimeException if set option for CURL fails.
     */
    private function setBody(RequestInterface $request)
    {
        $method = $request->getMethod();

        if ($method == 'POST' || $method == 'PUT') {
            $body = $request->getBody();

            if (!$body instanceof StreamInterface) {
                throw new \UnexpectedValueException('The body of request is not instance of StreamInterface');
            }

            $fields = (string)$body;

            if ($fields != '' && !curl_setopt($this->curl, CURLOPT_POSTFIELDS, $fields)) {
                throw new \RuntimeException('Set HTTP body error: ' . curl_error(), curl_errno());
            }
        }
    }

    /**
     * Set other options for CURL.
     */
    private function setOthers()
    {
        if (!curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true)) {
            throw new \RuntimeException('Set return transfer error: ' . curl_error(), curl_errno());
        }

        if (!curl_setopt($this->curl, CURLOPT_HEADER, true)) {
            throw new \RuntimeException('Set return header error: ' . curl_error(), curl_errno());
        }

        if (!curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout)) {
            throw new \RuntimeException('Set timeout error: ' . curl_error(), curl_errno());
        }
    }

    /**
     * Build the HTTP response instance from the CURL result.
     *
     * @param string $result The execute result of CURL.
     * @return Response The HTTP response instance.
     */
    private function buildResponse($result)
    {
        $response = new Response();
        $headerPart = substr($result, 0, $this->info['header_size']);
        $headerLines = explode("\r\n", $headerPart);

        foreach ($headerLines as $index => $headerLine) {
            $headerLine = trim($headerLine);

            if ($headerLine == '') {
                continue;
            }

            if ($index == 0) {
                list($version, $code, $phrase) = explode(' ', $headerLine);
                $response->withProtocolVersion(str_replace('HTTP/', '', $version));
                $response->withStatus(intval($code), $phrase);
            }

            list($name, $value) = explode(':', $headerLine);
            $response->withAddedHeader(trim($name), trim($value));
        }

        $stream = new Stream('php://memory', 'rw');

        if (!$stream->write(substr($result, $this->info['header_size']))) {
            throw new \RuntimeException('Write body to response error');
        }

        $stream->close();
        return $response->withBody($stream);
    }
}