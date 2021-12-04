<?php
/*
executes everything
*/
include 'Source.php';
include 'Message.php';

class App{
  public $methods = [];
  public $responses = [];
  public $source;
  public $middleware = [];
  private $currentGroup;

  function __construct($source) {
    set_error_handler(function($errno, $errstr, $errfile, $errline ){
        $response = new Response();
        $response->error($errno, $errstr, [
          'file' => $errfile,
          'line' => $errline,
          'backtrace' => debug_backtrace()
        ]);
        $this->source->respond($response->message());
        die;
    });
    $this->source = $source;
  }

  function run(){
    if( $this->isBatch($this->source->data) ){
      foreach ($this->source->data as $request) {
        $request = new Request($request);
        $response = new Response($request->id);

        $this->exec($request, $response);
      }
    }
    else {
      $request = new Request($this->source->data);
      $response = new Response($request->id);

      $this->exec($request, $response);
    }
    $this->respond();
  }

  function method($name, $requiredParams = [], $middlewares = [], $reference) {
    $method = new method($name, $requiredParams, $this->getMiddlewares($middlewares), $reference, $this->currentGroup);
    $this->methods[$this->getPath($method)] = $method;
    return $method;
  }

  function group($name, $middlewares = [], $callback) {
    $group = new Group($name, $this->getMiddlewares($middlewares), $this->currentGroup);
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
    foreach($middlewares as $name){
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
      $this->source->respond($this->responses);
    }
    else {
      $this->source->respond($this->responses[0]);
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

class Method{
  public $name;
  public $middlewares;
  public $reference;
  public $group;
  public $requiredParams;

  function __construct($name, $requiredParams = [], $middlewares=[], $reference, $group = null) {
    $this->name = $name;
    $this->middlewares = $middlewares;
    $this->reference = $reference;
    $this->group = $group;
    $this->requiredParams = $requiredParams;
  }

  function validate($request) {
    foreach ($this->requiredParams as $param){
      if(!isset($request->params[$param])){
        return false;
      }
    }
    return true;
  }

  function exec($request, $response){
    if(!$this->validate($request)){
      $response->error(-32602, 'invalidParams', ['required' => $this->requiredParams]);
    }
    if($this->group){
      $this->group->exec($request, $response);
    }
    if ($this->middlewares){
      foreach($this->middlewares as $middleware){
        call_user_func_array($middleware, [$request, $response]);
      }
    }
    call_user_func_array($this->reference, [$request, $response]);
  }
}

class Group{
  public $name;
  public $parent;
  public $middlewares;

  function __construct($name, $middlewares = [], $parent = null) {
    $this->name = $name;
    $this->middlewares = $middlewares;
    $this->parent = $parent;
  }

  function exec($request, $response){
    foreach ($this->middlewares as $middleware){
      call_user_func_array($middleware, [$request->params, $response]);
    }
  }
}
