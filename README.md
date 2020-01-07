# Crimson
A PHP Library to handle the HTTP requests in a clean way.

# Installation
The ``crimson`` can be installed using composer via the following command:
```bash
composer require malik/crimson
```

# Usage
The following example code display the message `'Hello, World!!'` when visited
to the `http://localhost:8080/foo` or `http://127.0.0.1:8080/foo`.

```PHP
use Crimson\RequestHandler;
use Crimson\HttpServer;
use Crimson\App;

class GetHandler extends RequestHandler {
  private $msg;

  public function initialize() {
    $this->msg = $this->getClassArgs()['msg'];
  }

  public function prepare() {
    // Called before the request methods.
  }

  public function get() {
    $this->setStatus(200);
    $this->setContentType('text/plain');
    $this->write($this->msg);
  }

  public function setDefaultHeaders() {
    $this->setHeader('Access-Control-Allow-Origin', '*');
  }

  public function onFinish() {
    // Called at the end of the request.
  }
}

$app = new App([
  ['\/foo', 'GetHandler', ['msg' => 'Hello, World!!']]
]);

$server = new HttpServer($app, [], '127.0.0.1', '8080');
$server->start();
```

# License
GNU General Public License v2.0
