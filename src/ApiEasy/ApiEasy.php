<?php
namespace ApiEasy;

use ApiEasy\Dispatcher\Dispatcher;
use ApiEasy\Dispatcher\DispatcherInterface;
use ApiEasy\Http\Message\Request;
use ApiEasy\Http\Message\Response;
use ApiEasy\Renderer\Renderer;
use ApiEasy\Renderer\RendererInterface;
use ApiEasy\Router\Router;
use ApiEasy\Router\RouterInterface;

/**
 * Representation of an API application
 *
 * @package ApiEasy
 */
class ApiEasy
{
    /**
     * Request instance
     *
     * @var Request
     * @access protected
     */
    protected $request = null;

    /**
     * Response instance
     *
     * @var Response
     * @access protected
     */
    protected $response = null;

    /**
     * Router instance
     *
     * @var RouterInterface
     * @access protected
     */
    protected $router = null;

    /**
     * Dispatcher instance
     *
     * @var DispatcherInterface
     * @access protected
     */
    protected $dispatcher = null;

    /**
     * Renderer  instance
     *
     * @var RendererInterface
     * @access protected
     */
    protected $renderer = null;

    /**
     * Controller namespace prefix
     *
     * @var string
     * @access protected
     */
    protected $callbackNs = '';

    /**
     * __construct
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->request    = new Request();
        $this->response   = new Response();
        $this->router     = new Router();
        $this->dispatcher = new Dispatcher();
        $this->renderer   = new Renderer();
    }

    /**
     * Register callback for HTTP GET method and the provided URI path.
     *
     * @param  string $path     The URI path.
     * @param  mixed  $callback Callback.
     * @access public
     * @return void
     */
    public function get($path, $callback)
    {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Register callback for HTTP POST method and the provided URI path.
     *
     * @param  string $path     The URI path.
     * @param  mixed  $callback Callback.
     * @access public
     * @return void
     */
    public function post($path, $callback)
    {
        $this->router->withRule('POST', $path, $callback);
    }

    /**
     * Register callback for HTTP PUT method and the provided URI path.
     *
     * @param  string $path     The URI path.
     * @param  mixed  $callback Callback.
     * @access public
     * @return void
     */
    public function put($path, $callback)
    {
        $this->router->withRule('PUT', $path, $callback);
    }

    /**
     * Register callback for HTTP DELETE method and the provided URI path.
     *
     * @param  string $path     The URI path.
     * @param  mixed  $callback Callback.
     * @access public
     * @return void
     */
    public function delete($uri, $callback)
    {
        $this->router->withRule('DELETE', $uri, $callback);
    }

    /**
     * Register callback for HTTP OPTIONS method and the provided URI path.
     *
     * @param  string $path     The URI path.
     * @param  mixed  $callback Callback.
     * @access public
     * @return void
     */
    public function options($uri, $callback)
    {
        $this->router->withRule('OPTIONS', $uri, $callback);
    }

    /**
     * Start handling HTTP request.
     *
     * @access public
     * @return void
     * @throw \RuntimeException if the method or uri is empty.
     */
    public function run()
    {
        $method = $this->request->getMethod();
        $uri    = $this->request->getUri();

        if ($method == '' || $uri === null) {
            throw new \RuntimeException('Get empty method or uri from Request');
        }

        $path  = $uri->getPath();
        $match = $this->router->route($method, $path);

        if ($match['callback'] == null) {
            $this->response->withStatus(404);
        } else {
            $this->request->withQueryParams($match['params']);
            $this->dispatcher->dispatch($match['callback'], $this->request, $this->response);
        }

        $this->renderer->render($this->response);
    }

    /**
     * Retrieves the router.
     *
     * @access public
     * @return RouterInterface The router instance.
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Return an instance with the router.
     *
     * @param  RouterInterface $router The router instance.
     * @access public
     * @return self
     */
    public function withRouter(RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Retrieves the dispatcher.
     *
     * @access public
     * @return DispatcherInterface The dispatcher instance.
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Return an instance with the dispatcher.
     *
     * @param  DispatcherInterface $dispatcher The dispatcher instance.
     * @access public
     * @return self
     */
    public function withDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Retrieves the renderer.
     *
     * @access public
     * @return RendererInterface The renderer instance.
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Return an instance with the renderer.
     *
     * @param  RendererInterface $renderer The renderer instance.
     * @access public
     * @return self
     */
    public function withRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Retrieves the callback namespace prefix.
     *
     * @access public
     * @return string The callback namespace prefix.
     */
    public function getCallbackNs()
    {
        return $this->callbackNs;
    }

    /**
     * Return an instance with the callback namespace prefix.
     *
     * @param  string $callbackNs The callback namespace prefix.
     * @access public
     * @return self
     */
    public function withCallbackNs($callbackNs)
    {
        $this->callbackNs = $callbackNs;
        return $this;
    }

    /**
     * Add callback for the provided method and uri path.
     *
     * @param  string $method   The HTTP method.
     * @param  string $path     The URI path.
     * @param  mixed  $callback Callback.
     * @access protected
     * @return void
     */
    protected function addRoute($method, $path, $callback)
    {
        $callback = $this->dispatcher->normalize($callback, $this->callbackNs);
        $this->router->withRule($method, $path, $callback);
    }
}
