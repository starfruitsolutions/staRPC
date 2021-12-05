<?php
/*
extends source adding http requests
*/

namespace StaRPC\Source;

class HTTP extends Base{

  function __construct() {
    $this->authentication = $_SERVER['HTTP_AUTHORIZATION'];

    // post
    if( $_SERVER['REQUEST_METHOD'] != 'POST' ){
      $response = new Response();
      $response->error(-32000 , 'Must be HTTP POST Request');
      $this->respond($response->message());
    }

    // content-type
    if( !in_array($_SERVER['HTTP_CONTENT_TYPE'], ['application/json','application/json-rpc'], true ) ){
      $response = new Response();
      $response->error(-32000 , 'Invalid Content-Type');
      $this->respond($response->message());
    }

    // data
    $input = file_get_contents('php://input');

    // parse
    $this->parse($input);
  }

  function respond($content){
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($content);
    die;
  }
}
