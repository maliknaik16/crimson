<?php

namespace Crimson;

/**
 * @file
 * Contains HttpServer class.
 */

use React\Http\Server;
use React\Socket\Server as Socket;
use React\Http\Response;
use React\EventLoop\Factory;
use Psr\Http\Message\ServerRequestInterface;

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
   * The Socket server.
   *
   * @var \React\Socket\Server
   */
  protected $socket;

  /**
   * The Http server.
   *
   * @var \React\Http\Server
   */
  protected $server;

  /**
   * The App.
   *
   * @var \Crimson\App
   */
  protected $app;

  /**
   * An array of TLS options to pass to the server.
   *
   * @var array
   * @see https://www.php.net/manual/en/context.ssl.php for TLS Context options.
   */
  protected $tls_options;

  /**
   * Initialize the Http server.
   *
   * @param \Crimson\App $app
   *   The application object.
   * @param array $tls_options
   *   The TLS options for the server.
   * @param string $address
   *   The address of the server.
   * @param int $port
   *   The port number.
   */
  public function __construct(App $app, array $tls_options = [], $address = '127.0.0.1', $port = 80) {

    $this->loop = Factory::create();
    $this->tls_options = $this->getTlsOptions($tls_options);

    try {

      if ($app == NULL) {
        throw new \Exception('The App class is not initialized.');
      } else {
        $this->app = $app;
      }

      $this->socket = new Socket($address . ':' . $port, $this->loop, $this->tls_options);
      $this->server = new Server(function (ServerRequestInterface $request) {
        return $this->handleRequest($request);
      });

    } catch (\Exception $e) {
      echo 'Caught Exception: ' . $e->getMessage() . PHP_EOL;
    }
  }

  /**
   * Returns the tls options.
   *
   * @param array $tls_options
   *   The tls options.
   *
   * @return array
   */
  public function getTlsOptions(array $tls_options) {
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

    return $tls_opts;
  }

  /**
   * Handles the incoming requests.
   *
   * @param Psr\Http\Message\ServerRequestInterface $request
   *   The request object.
   *
   * @return React\Http\Response
   */
  public function handleRequest(ServerRequestInterface $request) {
    $matched = $this->app->match($request);

    if($matched === FALSE) {
      return new Response(404, [], 'Page not found.');
    }

    $handler = $this->app->getHandler();

    try {
      if (is_string($handler)) {
        throw new \Exception($handler);
      }
    }catch(\Exception $e) {
      echo 'Caught Exception: ' . $e->getMessage() . PHP_EOL;
    }

    $method = strtolower($request->getMethod());
    $handler->initialize();

    if (!$handler->hasFinished()) {
      $handler->prepare();
    }

    if (!$handler->hasFinished()) {
      if ($method == 'get') {
        $handler->get();
      } elseif ($method == 'post') {
        $handler->post();
      } elseif ($method == 'head') {
        $handler->head();
      } elseif ($method == 'delete') {
        $handler->delete();
      } elseif ($method == 'patch') {
        $handler->patch();
      } elseif ($method == 'put') {
        $handler->put();
      } elseif ($method == 'options') {
        $handler->options();
      }

      if (!$handler->hasFinished()) {
        $handler->setDefaultHeaders();
        $handler->onFinish();
      }
    }

    if(!$handler->hasFinished()) {
      $handler->finish();
    }

    $response = $handler->finish();

    if (!is_null($response)) {
      return $response;
    }

    return new Response(404);
  }

  /**
   * Starts listening to the socket and runs the Event loop.
   */
  public function start() {
    $this->server->listen($this->socket);
    $this->loop->run();
  }

  /**
   * Stops the Event loop.
   */
  public function stop() {
    $this->loop->stop();
  }

}
