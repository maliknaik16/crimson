<?php

namespace Crimson;

/**
 * @file
 * Contains Http Server class.
 */

use React\Http\Server;
use React\Socket\Server as Socket;
use React\Http\Response;
use React\EventLoop\Factory;

use Psr\Http\Message\ServerRequestInterface;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use function GuzzleHttp\Psr7\parse_query;

/**
 * Responsible for the creation of the HTTP server.
 */
class HttpServer {

  /**
   * The EventLoop.
   *
   * @var \React\EventLoop\Factory
   */
  protected $loop;

  /**
   * The EventLoop.
   *
   * @var \React\Socket\Server
   */
  protected $socket;

  /**
   * The EventLoop.
   *
   * @var \React\Http\Server
   */
  protected $server;

  /**
   * The Application.
   *
   * @var \ReactRequest\Application
   */
  protected $app;

  /**
   * An array of TLS options to pass to the server.
   *
   * @var array
   * @see https://www.php.net/manual/en/context.ssl.php for TLS Context options.
   */
  protected $tls_options;

  public function __construct(Application $app, $tls_options = [], $address = '127.0.0.1', $port = 80) {

    $this->loop = Factory::create();

    $tls_opts = [];
    if (is_array($tls_options) && !empty($tls_options)) {

      // The address must have tls:// scheme in order to work.
      if (isset($tls_options['tls'])) {
        $tls_opts = $tls_options['tls'];
      } else {
        $tls_opts = [
          'tls' => $tls_options,
        ];
      }
    }

    $this->tls_options = $tls_opts;

    try {

      if($app == null) {
        throw new \Exception('The Application class is not initialized.');
      }else{
        $this->app = $app;
      }

      $this->socket = new Socket($address . ':' . $port, $this->loop, $this->tls_options);
      $this->server = new Server(function (ServerRequestInterface $request) {
        //return $this->handleRequest($request);
        /*$route = $this->app->getRouteParameters();

        if($route instanceof Response) {
          return $route;
        }*/

        $this->app->match($request);

        try {
          $handler = $this->app->getHandler();

          if(is_string($handler)) {
            throw new \Exception($handler);
          }

          //$handler->setArguments($this->app->getClassArgs());

          $method = strtolower($request->getMethod());

          $handler->initialize();
          $handler->prepare();

          if(!$handler->hasFinished()) {
            if($method == 'get') {
              $handler->get();
            }else if($method == 'post') {
              $handler->post();
            }else if($method == 'head') {
              $handler->head();
            }else if($method == 'delete') {
              $handler->delete();
            }else if($method == 'patch') {
              $handler->patch();
            }else if($method == 'put') {
              $handler->put();
            }else if($method == 'options') {
              $handler->options();
            }

            if(!$handler->hasFinished()) {
              $handler->setDefaultHeaders();
              $handler->onFinish();
            }

            $response = $handler->finish();
          }

          if(!is_null($response)) {
            return $response;
          }
        }catch(\Exception $e) {
          echo 'Caught Exception: ' . $e->getMessage() . PHP_EOL;
        }

        return new Response(404);
      });
    }catch(\Exception $e) {
      echo 'Caught Exception: ' . $e->getMessage() . PHP_EOL;
    }
  }

  public function handleRequest(ServerRequestInterface $request) {
    return new Response(200, [], 'Hello, World');
  }

  public function start() {
    $this->server->listen($this->socket);
    $this->loop->run();
  }

  public function stop() {
    $this->loop->stop();
  }

  /**
   * The route match function.
   */
  public function match($path) {
    $context = new RequestContext;
    $matcher = new UrlMatcher($this->app->routes, $context);

    return $matcher->match($path);
  }

}
