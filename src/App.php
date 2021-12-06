<?php
/*
executes everything
*/

namespace StaRPC;
use StaRPC\Message as Message;

class App{
  private static $instance = null; // Hold the class instance.
  public $channel;
  public $source;
  public $methods = [];
  public $responses = [];
  public $middleware = [];
  private $currentGroup;


  private function __construct() {
    set_error_handler(function($errno, $errstr, $errfile, $errline ){
        $response = new Message\Response();
        $response->error($errno, $errstr, [
          'file' => $errfile,
          'line' => $errline,
          'backtrace' => debug_backtrace()
        ]);
        $this->channel->respond($response->message());
        die;
    });
  }

  public static function get(){

    if(!self::$instance)
    {
      self::$instance = new App();
    }

    return self::$instance;
  }

  function channel($channel){
    $this->channel = $channel;
  }

  function source($source){
    $this->source = $source;
  }

  function run(){
    if( $this->isBatch($this->channel->data) ){
      foreach ($this->channel->data as $request) {
        $request = new Message\Request($request);
        $response = new Message\Response($request->id);

        $this->exec($request, $response);
      }
    }
    else {
      $request = new Message\Request($this->channel->data);
      $response = new Message\Response($request->id);

      $this->exec($request, $response);
    }
    $this->respond();
  }

  function method($name, $requiredParams = [], $middlewares = [], $reference) {
    $method = new Procedure\Method($name, $requiredParams, $this->getMiddlewares($middlewares), $reference, $this->currentGroup);
    $this->methods[$this->getPath($method)] = $method;
    return $method;
  }

  function group($name, $middlewares = [], $callback) {
    $group = new Procedure\Group($name, $this->getMiddlewares($middlewares), $this->currentGroup);
    $this->currentGroup = $group;
    call_user_func($callback, $this);
    $this->currentGroup = null;
    return $group;
  }

  function middleware($name, $reference) {
    $this->middlewares[$name] = $reference;
  }

  function getPath($method){
    $path = $method->name;
    $group = $method->group ?? false;
    while ($group){
      $path = $group->name . $path;
      $group = $group->parent ?? false;
    }
    return $path;
  }

  function getMiddlewares($names = []) {
    $middlewares = [];
    foreach($names as $name){
      $middlewares[] = $this->middlewares[$name];
    }
    return $middlewares;
  }

  function exec($request, $response){
    if (!$request->isValid()) {
      $response->error(-32600, 'Invalid Request');
      $this->addResponse($response);
      return;
    }
    if (!isset($this->methods[$request->method])){
      $response->error(-32601, 'Method not found');
      $this->addResponse($response);
      return;
    }

    $this->methods[$request->method]->exec($request, $response);
    $this->responses[] = $response->message();
  }

  function addResponse($response){
    $this->responses[] = $response->message();
  }

  function respond(){
    if (!$this->responses) {
      die();
    }
    else if(count($this->responses) >= 1) {
      $this->channel->send($this->responses);
    }
    else {
      $this->channel->send($this->responses[0]);
    }
  }

  function isBatch(array $inpt_arr): bool {
    // An empty array is in theory a valid associative array
    // so we return 'true' for empty.
    if ([] === $inpt_arr) {
      return false;
    }
    $n = count($inpt_arr);
    for ($i = 0; $i < $n; $i++) {
      if(!array_key_exists($i, $inpt_arr)) {
        return false;
      }
    }
    // Dealing with a Sequential array
    return true;
  }
}
