<?php
/*
Base class for data source
*/

namespace StaRPC\Source;
use \PDO;

class MySQL extends Base{
  public $db; //database object

  function connect($config){
    $this->db = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['user'], $config['password'], array(
            PDO::ATTR_PERSISTENT     => false,
            PDO::ATTR_ERRMODE        => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ));
  }

  function validate($request){
    if($request['sql']){
      return true;
    }
    return false;
  }

  function exec($request){
    $call = $this->db->prepare($request['sql']);
    $call->execute($request['params'] ?? null);
    if($call->rowCount()){
      return $call->fetchAll();
    }
      return 'Success';
  }

}
