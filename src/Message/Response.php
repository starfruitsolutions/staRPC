<?php
/*
Class for outgoing json-rpc responses
*/
namespace StaRPC\Message;

class Response{
  public $version;
  public $id;
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
