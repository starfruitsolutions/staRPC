<?php
/*
Base class for json-rpc messaging
*/

class Message {
  public $version;
  public $id;

  function __construct() {
    pass;
  }
}

class Request extends Message{
  public $method;
  public $params;

  function __construct($message) {
    //  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
    $this->version = $message['jsonrpc'] ?? false;
    $this->method = $message['method'] ?? false;

    $this->id = $message['id'] ?? null;

  }

  function isValid() {
    return ($this->version == '2.0' && $this->method);
  }
}

class Response extends Message{
  public $result;
  public $error;

  function __construct($id=null) {

    $this->version = '2.0';
    $this->id = $id;

  }

  function error($code, $message, $data=[]){
    $this->error=[
      'code' => $code,
      'message' => $message,
      'data'=> $data
    ];
    return $this->message();
  }

  function result($result){
    $this->result = $result;
    return $this->message();
  }

  function message(){
    $message = [
      'jsonrpc' => $this->version,
      'id' => $this->id
    ];
    if($this->error){
      $message['error'] = $this->error;
    }
    else{
      $message['result'] = $this->result;
    }
    return $message;
  }

}
