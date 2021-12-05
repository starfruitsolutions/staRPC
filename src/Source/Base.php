<?php
/*
Base class for data source
*/

namespace StaRPC\Source;

class Base{

  function request($request){
    if($this->validate($request)){
      return $this->exec($request);
    }
  }
  function validate($request){
    return true;
  }
  function exec($request){
    return 'data';
  }

}
