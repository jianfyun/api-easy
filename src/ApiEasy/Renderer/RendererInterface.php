<?php
namespace ApiEasy\Renderer;

use ApiEasy\Http\Message\Response;

/**
 * This interface defines the common methods for renderers.
 * 
 * @package ApiEasy\Renderer
 */
interface RendererInterface
{
    /**
     * Render the HTTP response.
     * 
     * @param Response $response The HTTP Response instance.
     * @access public
     * @return void
     */
    public function render(Response $response);
}
