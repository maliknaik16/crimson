<?php

namespace Crimson;

/**
 * @file
 * Contains Application class.
 */

use Psr\Http\Message\ServerRequestInterface;

/**
 * The Base class for HTTP request handlers.
 */
class Application {

  /**
   * Stores an array of handlers along with the routes.
   */
  private $matcher;

  /**
   * Stores all the routes.
   */
  private $handlers;

  /**
   * Stores the name of the handler class name.
   */
  private $handler_class;

  /**
   * Initialize the object.
   *
   * @param array $handlers
   */
  public function __construct(array $handlers) {
    $this->handlers = $handlers;
  }

  public function match(ServerRequestInterface $request) {
    $path = $request->getUri()->getPath();
    $found = false;
    $route_args = [];
    $class_args = [];

    foreach($this->handlers as $handler) {

      // The first element must be string with regular expression.
      if(isset($handler[0]) && is_string($handler[0])) {
        $pattern = $handler[0];

        $pattern = '/' . $pattern . '/';
        if(preg_match($pattern, $path, $matches)) {
          $found = true;
          $route_args = $matches;
        }
      }else{
        throw new \Exception('The first element of each handler must be a string representing a regex.');
      }

      if($found === true) {
        if(isset($handler[1]) && is_string($handler[1]) && class_exists($handler[1])) {
          $handler_class = $handler[1];
        }else{
          throw new \Exception('The second element of handler must be a class name.');
        }

        if(isset($handler[2]) && is_array($handler[2])) {
          $class_args = $handler[2] ?: [];
        }

        $this->handler_class = new $handler_class($request, $class_args);
        $this->handler_class->setRouteArgs($route_args);
        break;
      }
    }
  }

  public function getHandler() {

    if(is_null($this->handler_class)) {
      return 'The handler class is not initialized.';
    }
    if (!($this->handler_class instanceof RequestHandler)) {
      return 'All handler must implement Crimson\RequestHandler class';
    }

    return $this->handler_class;
  }

  /**
   * Get class arguments.
   */
  public function getClassArgs() {
    return $this->class_args;
  }

  /**
   * Get the route args.
   */
  public function getRouteArgs() {
    return $this->route_args;
  }

}
