<?php
/*
procedure method
*/

namespace StaRPC\Procedure;

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
