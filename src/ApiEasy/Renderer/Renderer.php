<?php
namespace ApiEasy\Renderer;

use ApiEasy\Http\Message\Response;

/**
 * Representation of an HTTP response renderer.
 *
 * @uses    RendererInterface
 * @package ApiEasy\Renderer
 */
class Renderer implements RendererInterface
{
    /**
     * Render the HTTP response.
     *
     * @param Response $response The HTTP Response instance.
     * @access public
     * @return void
     */
    public function render(Response $response)
    {
        $version = $response->getProtocolVersion();
        $status = $response->getStatusCode();
        $phrase = $response->getReasonPhrase();
        header("HTTP/$version $status $phrase");

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        echo $response->getBody();
    }
}
