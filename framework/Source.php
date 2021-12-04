<?php
/*
Base class for data transmission sources
*/
class Source{
  public $authentication;
  public $data;

  function respond($content){
    echo $content;
  }

  function parse($input){
    $this->data = json_decode($input, true);
    if( !$this->data ){ // false or empty
      $response = new Response();
      if($this->data === []){
        $response->error(-32600 , 'Invalid Request');
      }
      else {
        $response->error(-32700 , 'Parse Error');
      }
      $this->respond($response->message());
    }
  }

}
