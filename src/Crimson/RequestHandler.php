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
   * The response message.
   *
   * @var string
   */
  protected $message;

  /**
   * Stores boolean value whether finish() method is invoked.
   *
   * @var bool
   */
  protected $finished;

  /**
   * The arguments for the constructor of the request handler.
   *
   * @var array
   */
  private $class_args;

  /**
   * The route arguments.
   *
   * @var array
   */
  private $route_args;

  /**
   * Initialize the object.
   *
   * @param \Psr\Http\Message\ServerRequestInterface $request
   *   The request object.
   * @param array $class_args
   *   The class arguments.
   */
  final public function __construct(ServerRequestInterface $request, array $class_args = []) {
    $this->request = $request;
    $this->status_code = 500;
    $this->headers = [];
    $this->finished = FALSE;
    $this->message = '';
    $this->class_args = $class_args;
  }

  /**
   * First method to be called when the request is received.
   *
   * Override this method to initialize instead of __construct().
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
   * When this method is called in initialize() or prepare() method then the
   * response is retured. If this method is called in any of the request methods
   * then the setDefaultHeaders() and onFinish() methods are not invoked and the
   * response is returned.
   *
   * @return bool|React\Http\Response
   */
  final public function finish() {
    if ($this->finished == TRUE) {
      return new Response($this->status_code, $this->headers, $this->message);
    }
    $this->finished = TRUE;
  }

  /**
   * Sets the Content-Type header.
   *
   * @param string $content_type
   */
  final public function setContentType($content_type) {
    $this->setHeader('Content-Type', $content_type);
  }

  /**
   * Returns whether finish() method is invoked/called.
   */
  final public function hasFinished() {
    return $this->finished;
  }

  /**
   * Redirects to the specified url.
   *
   * @param string $url
   *   The url to redirect to.
   * @param bool $permanent
   *   Whether to redirect temporarily or permanently.
   * @param int $status_code
   *   The status code for the response.
   */
  final public function redirect($url, $permanent = FALSE, $status_code = 302) {
    if (count($this->headers) > 0) {
      throw new \Exception('Cannot redirect when the headers are already written.');
    }

    if ($status_code == 302) {
      $status_code = $permanent ? 301 : 302;
    }

    if (!($status_code >= 300 && $status_code <= 399)) {
      throw new \Exception('The status code for the redirect must be between 300 and 399 inclusive.');
    }

    $this->setStatus($status_code);
    $this->setHeader('Location', $url);
    $this->finish();
  }

  /**
   * Clears all the response headers.
   */
  final public function clearHeaders() {
    $this->headers = [];
  }

  /**
   * Override this method to handle request with GET request method.
   */
  public function get() {
  }

  /**
   * Override this method to handle request with HEAD request method.
   */
  public function head() {
  }

  /**
   * Override this method to handle request with POST request method.
   */
  public function post() {
  }

  /**
   * Override this method to handle request with DELETE request method.
   */
  public function delete() {
  }

  /**
   * Override this method to handle request with PATCH request method.
   */
  public function patch() {
  }

  /**
   * Override this method to handle request with PUT request method.
   */
  public function put() {
  }

  /**
   * Override this method to handle request with OPTIONS request method.
   */
  public function options() {
  }

  /**
   * Override this method to handle request with the request method that is
   * not defined in this class.
   */
  public function httpMethod() {
  }

  /**
   * Sets the route arguments.
   *
   * @param array $args
   *   The route arguments to set.
   */
  public function setRouteArgs(array $args) {
    $this->route_args = $args;
  }

  /**
   * Returns the route arguments.
   *
   * @return array
   */
  final public function getRouteArgs() {
    return $this->route_args;
  }

  /**
   * Returns the arguments passed to the constructor of the handler.
   *
   * @return array
   */
  final public function getClassArgs() {
    return $this->class_args;
  }

  /**
   * Sends an 5xx or 4xx error message.
   *
   * @param int $status_code
   *   The status code to return.
   * @param string $msg
   *   The message to return in response.
   */
  final public function sendError($status_code = 500, $msg = 'Unknown') {
    $this->setStatus($status_code);
    $this->message = 'HTTP ' . $status_code . ': ' . $msg;
    $this->finish();
  }

  /**
   * Returns the request object.
   *
   * @return \React\Http\ServerRequestInterface $request
   *   The request object.
   */
  final public function getRequest() {
    return $this->request;
  }

  /**
   * Returns the query.
   */
  final public function getQuery() {
    return $this->request->getUri()->getQuery();
  }

  /**
   * Called after the request method function is executed.
   *
   * Override this method to set headers for the response.
   */
  public function setDefaultHeaders() {
  }

  /**
   * Sets the header for the response. Replaces the header value if the header
   * name already exists.
   *
   * @param string $name
   *   The header name.
   * @param string|array $value
   *   The header value.
   */
  final public function setHeader($name, $value) {
    if (array_key_exists($name, $this->headers)) {
      $this->headers[$name] = $value;
    } else {
      $this->headers = array_merge($this->headers, [$name => $value]);
    }
  }

  /**
   * Sets the status code for the response.
   *
   * @param int $status_code
   *   The status code.
   */
  final public function setStatus($status_code) {
    $this->status_code = $status_code;
  }

  /**
   * Returns the status code for the response.
   *
   * @return int
   */
  final public function getStatus() {
    return $this->status_code;
  }

  /**
   * Responds with the given message.
   *
   * @param mixed|string $message
   *   The response message.
   */
  final public function write($message) {
    $this->message = $message;
  }

}
