<?php

namespace Crimson;

/**
 * @file
 * Contains RequestHandler class.
 */

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

/**
 * The Base class for HTTP request handlers.
 */
abstract class RequestHandler {

  /**
   * The Request object.
   *
   * @var \Psr\Http\Message\ServerRequestInterface
   */
  protected $request;

  /**
   * The headers for the response.
   *
   * @var array
   */
  protected $headers;

  /**
   * The response status code.
   *
   * @var int
   */
  protected $status_code;

  /**
   * The response status code.
   *
   * @var string
   */
  protected $message;

  /**
   * Has the finished function invoked.
   *
   * @var boolean
   */
  protected $finished;

  /**
   * The arguments for the constructor of the request handler.
   */
  private $class_args;

  /**
   * The arguments for the constructor of the request handler.
   */
  private $route_args;

  /**
   * Initialize the Web Thing Server.
   */
  public final function __construct(ServerRequestInterface $request, $class_args = []) {
    $this->request = $request;
    $this->status_code = 200;
    $this->headers = [];
    $this->finished = false;
    $this->message = '';
    $this->class_args = $class_args;
  }

  /**
   * First method to be called when the request is received.
   * Override this method to initialize data.
   */
  public function initialize() {
  }

  /**
   * Called at the beginning of a request before get, post, put, etc.
   *
   * Override this method to perform common initialization regardless of the
   * request method.
   */
  public function prepare() {
  }

  /**
   * Called after the end of a request.
   */
  public function onFinish() {
  }

  /**
   * Terminate the request.
   */
  public final function finish() {
    $this->finished = true;
    return new Response($this->status_code, $this->headers, $this->message);
  }

  /**
   * Called after the end of a request.
   */
  public final function hasFinished() {
    return $this->finished;
  }

  /**
   * Redirects to the specified url.
   */
  public final function redirect($url, $permanent = false, $status_code = 302) {
    if(count($this->headers) > 0) {
      throw new \Exception('Cannot redirect when the headers are already written.');
    }

    if($status_code == 302) {
      $status_code = $permanent ? 301 : 302;
    }

    if(!($status_code >= 300 && $status_code <= 399)) {
      throw new \Exception('The status code for the redirect must be between 300 and 399 inclusive.');
    }

    $this->setStatusCode($status_code);
    $this->setHeader('Location', $url);
    $this->finish();
  }

  /**
   * Handles the GET request.
   */
  public function get() {
  }

  /**
   * Handles the HEAD request.
   */
  public function head() {
  }

  /**
   * Handles the POST request.
   */
  public function post() {
  }

  /**
   * Handles the DELETE request.
   */
  public function delete() {
  }

  /**
   * Handles the PATCH request.
   */
  public function patch() {
  }

  /**
   * Handles the PUT request.
   */
  public function put() {
  }

  /**
   * Handles the OPTIONS request.
   */
  public function options() {
  }

  /**
   * Sets the Query and Symfony's route related arguments/parameters.
   */
  public function setRouteArgs(array $args) {
    $this->route_args = $args;
  }

  /**
   * Returns the arguments
   */
  public final function getRouteArgs() {
    return $this->route_args;
  }

  /**
   * Returns the arguments
   */
  public final function getClassArgs() {
    return $this->class_args;
  }

  /**
   * HTTPError
   */
  public final function sendError($status_code = 500, $msg = '') {
    $this->setStatusCode($status_code);
    $this->message = $msg;
    $this->finish();
  }

  /**
   * Returns the request object.
   *
   * @return \React\Http\ServerRequestInterface
   */
  public final function getRequest() {
    return $this->request;
  }

  /**
   * Returns the query
   */
  public final function getQuery() {
    return $this->request->getUri()->getQuery();
  }

  /**
   * Sets the headers for the request.
   */
  public function setDefaultHeaders() {
  }

  /**
   * Sets the headers for the response
   */
  public final function setHeader($name, $value) {
    if(array_key_exists($name, $this->headers)) {
      $this->headers[$name] = $value;
    }else{
      $this->headers += [$name => $value];
    }
  }

  /**
   * Sets the status code for the response.
   */
  public final function setStatusCode($status_code) {
    $this->status_code = $status_code;
  }

  /**
   * Returns the status code for the response.
   */
  public final function getStatusCode() {
    return $this->status_code;
  }

  /**
   * Responds with the given message.
   */
  public final function write($message) {
    $this->message = $message;
  }
}
