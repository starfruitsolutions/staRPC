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
    try {
      if(!$this->validate($request)){
        $response->error(-32602, 'invalidParams', ['required' => $this->requiredParams]);
      }
      if($this->group){
        $this->group->exec($request, $response);
      }
      if ($this->middlewares){
        foreach($this->middlewares as $middleware){
          $middleware($request, $response);
        }
      }
      $reference = $this->reference;
      $reference($request, $response);
    }
    catch (\Exception $e) {
      $response->error($e->getCode(), $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
      ]);
      //print_r($response);
    }

  }
}
