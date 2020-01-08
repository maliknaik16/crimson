<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Crimson\RequestHandler;
use Crimson\HttpServer;
use Crimson\App;

/**
 * This example illustrates how to get the captured groups(`<group_name>`
 * and `<group_id>`) from the regular expression and return as response.
 *
 * For example, When `http://localhost:8080/groups/mygroup/100` is accessed then
 * the response of the request will be:
 *   Group Name: mygroup
 *   Group ID: 100
 */
class GroupHandler extends RequestHandler {

  private $matches;

  /**
   * Called before the request methods.
   */
  public function prepare() {
    // The `getRouteArgs()` returns the matches array from the regular expression.
    $this->matches = $this->getRouteArgs();
  }

  /**
   * Called when the request method is GET.
   */
  public function get() {
    $msg = sprintf("Group Name: %s\nGroup ID: %d", $this->matches['group_name'], $this->matches['group_id']);

    $this->setStatus(200);
    $this->setContentType('text/plain');
    $this->write($msg);
  }
}

$app = new App([
  ['\/groups\/(?P<group_name>\w+)\/(?P<group_id>\d+)\/?', 'GroupHandler']
]);

$server = new HttpServer($app, [], '127.0.0.1', '8080');

// Start the server
$server->start();
