<?php

namespace Crimson;

/**
 * @file
 * Contains App class.
 */

use Psr\Http\Message\ServerRequestInterface;

/**
 * The App class responsible for managing the request handlers.
 */
class App {

  /**
   * Stores all the handlers.
   *
   * @var array
   *   The array of handlers.
   */
  private $handlers;

  /**
   * The RequestHandler instance.
   *
   * @var Crimson\RequestHandler
   *   The instance of request handler class.
   */
  private $handler_class;

  /**
   * Initialize the object.
   *
   * @param array $handlers
   *   The array of request handlers.
   */
  public function __construct(array $handlers) {
    $this->handlers = $handlers;
    $this->handler_class = NULL;
  }

  /**
   * Matches the path with the regex from the $handlers, initializes the handler
   * class and returns whether a match is found or not.
   *
   * @param Psr\Http\Message\ServerRequestInterface $request
   *   The request object.
   *
   * @return bool
   */
  public function match(ServerRequestInterface $request) {
    $path = $request->getUri()->getPath();
    $found = FALSE;
    $matches = FALSE;
    $route_args = [];
    $class_args = [];

    foreach ($this->handlers as $handler) {

      // The first element must be string with regular expression.
      if (isset($handler[0]) && is_string($handler[0])) {
        $pattern = $handler[0];

        $pattern = '/^' . $pattern . '$/';
        if (preg_match($pattern, $path, $matches)) {
          $found = TRUE;
          $route_args = $matches;
        }
      } else {
        throw new \Exception('The first element of each handler must be a string representing a regex.');
      }

      if ($found === TRUE) {
        if (isset($handler[1]) && is_string($handler[1]) && class_exists($handler[1])) {
          $handler_class = $handler[1];
        } else {
          throw new \Exception('The second element of handler must be a class name.');
        }

        if (isset($handler[2]) && is_array($handler[2])) {
          $class_args = $handler[2] ?: [];
        }

        $this->handler_class = new $handler_class($request, $class_args);
        $this->handler_class->setRouteArgs($route_args);
        break;
      }
    }

    if ($found === TRUE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns the initialized handler or exception message.
   *
   * @return string|Crimson\RequestHandler
   */
  public function getHandler() {

    if (is_null($this->handler_class)) {
      return 'The handler class is not initialized.';
    }
    if (!($this->handler_class instanceof RequestHandler)) {
      return 'All handler must implement Crimson\RequestHandler class';
    }

    return $this->handler_class;
  }

  /**
   * Merges the provided handlers with the handlers already present.
   *
   * @param array $handlers
   *   The array of handlers to add to the current handler.
   */
  public function addHandlers(array $handlers) {
    if (is_array($handlers[0])) {
      $this->handlers = array_merge($this->handlers, $handlers);
    } else {
      array_push($this->handlers, $handlers);
    }
  }

}
