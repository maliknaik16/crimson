# Crimson
A PHP Library to handle the HTTP requests in a clean way. This library is built
on top of [ReactPHP](https://reactphp.org/).

# Installation
The ``crimson`` can be installed using composer via the following command:
```bash
composer require malik/crimson:^1.0.0
```

# Usage
The following example code display the message `'Hello, World!!'` when
`http://localhost:8080/foo` or `http://127.0.0.1:8080/foo` is accessed with the GET request.

```PHP
<?php

use Crimson\RequestHandler;
use Crimson\HttpServer;
use Crimson\App;

/**
 * Every handler class must extend the Crimson\RequestHandler class and
 * override methods.
 */
class GetHandler extends RequestHandler {

  private $msg;

  /**
   * The first method to be called from the handler class. Use this method to do
   * initialization instead of using a constructor.
   */
  public function initialize() {
    $this->msg = $this->getClassArgs()['msg'];
  }

  /**
   * Called before the request methods.
   */
  public function prepare() {
  }

  /**
   * Called when the request method is GET.
   */
  public function get() {
    $this->setStatus(200);
    $this->setContentType('text/plain');
    $this->write($this->msg);
  }

  /**
   * Use this method to set the response headers
   */
  public function setDefaultHeaders() {
    $this->setHeader('Access-Control-Allow-Origin', '*');
  }

  /**
   * This method is called at the end of the request.
   */
  public function onFinish() {
  }
}

/**
 * The Crimson\App class takes only 1 argument that is an array.
 *
 * Each element of the App class argument should be an array with 3 elements:
 *   1. RegEx pattern representing the path to invoke the handler class methods.
 *   2. Name of the handler class.
 *   3. An array which will be passed to the constructor of the handler class
 *      and can be accessed by `getClassArgs()` method.
 */
$app = new App([
  ['\/foo', 'GetHandler', ['msg' => 'Hello, World!!']]
]);

/**
 * The HttpServer class takes 4 arguments:
 *   1. The instance of the App class(required).
 *   2. Array of TLS options.
 *   3. Address to access the Http server.
 *   4. The port on which the server listens to.
 */
$server = new HttpServer($app, [], '127.0.0.1', '8080');

// Start the server
$server->start();
```

For more examples, check the `examples/` directory in this repository.

# License
GNU General Public License v2.0
