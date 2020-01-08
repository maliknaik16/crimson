<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Crimson\RequestHandler;
use Crimson\HttpServer;
use Crimson\App;

/**
 * This example illustrates the usage of the redirect() method of the
 * \Crimson\RequestHandler class.
 *
 * The following code redirects the user to the `uri` specified in the query
 * string of the URI. For example, When the user tries to access
 * `http://localhost:8080/redirect?uri=https://google.com` then the user is
 * redirected to `https://google.com`.
 *
 * If the uri key is not specified in the URI then the user stays on the same
 * page and receives a response of 200 with a message `The URI is not specified.`
 */
class RedirectHandler extends RequestHandler {

  private $uri;

  /**
   * Called before the request methods.
   */
  public function prepare() {
    $query_params = $this->getRequest()->getQueryParams();
    $this->uri = array_key_exists('uri', $query_params) ? $query_params['uri'] : FALSE;
  }

  /**
   * Called when the request method is GET.
   */
  public function get() {
    if($this->uri === FALSE) {
      $this->setStatus(200);
      $this->setContentType('text/plain');
      $this->write('The URI is not specified.');
    }else{
      $this->redirect($this->uri, true, 301);
    }
  }
}

$app = new App([
  ['\/redirect', 'RedirectHandler']
]);

$server = new HttpServer($app, [], '127.0.0.1', '8080');

// Start the server
$server->start();
