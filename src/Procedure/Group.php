<?php
/*
procedure groups
*/

namespace StaRPC\Procedure;

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
    if($this->parent){
      $this->parent->exec($request, $response);
    }
    foreach ($this->middlewares as $middleware){
      call_user_func_array($middleware, [$request, $response]);
    }
  }
}
