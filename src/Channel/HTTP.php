<?php
/*
extends Channel adding http requests and responses
*/

namespace StaRPC\Channel;
use \StaRPC\Message as Message;

class HTTP extends Base{

  function __construct() {
    $this->authentication = $_SERVER['HTTP_AUTHORIZATION']?? false;

    $this->validate();

    // data
    $input = file_get_contents('php://input');

    // parse
    $this->parse($input);
  }

  function validate(){
    // post
    if( $_SERVER['REQUEST_METHOD'] != 'POST' ){
      $response = new Message\Response();
      $response->error(-32000 , 'Must be HTTP POST Request');
      $this->send($response->message());
    }

    // content-type
    if( !in_array($_SERVER['HTTP_CONTENT_TYPE'], ['application/json','application/json-rpc'], true ) ){
      $response = new Message\Response();
      $response->error(-32000 , 'Invalid Content-Type');
      $this->send($response->message());
    }

  }

  function send($content){
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($content);
    die;
  }
}
