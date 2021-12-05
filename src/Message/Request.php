<?php
/*
Class for incoming json-rpc requests
*/
namespace StaRPC\Message;

class Request{
  public $version;
  public $id;
  public $method;
  public $params;

  function __construct($message) {
    //  {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request"}, "id": null}
    $this->version = $message['jsonrpc'] ?? false;
    $this->method = $message['method'] ?? false;
    $this->params = $message['params'] ?? [];

    $this->id = $message['id'] ?? null;

  }

  function isValid() {
    return ($this->version == '2.0' && $this->method);
  }
}
