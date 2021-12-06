<?php
/*
  allows getting and setting of custom global properties
*/

namespace StaRPC;

class Container{
  protected $properties = [];

  public function __get($property) {
   return $this->properties[$property] ?? false;
 }

 public function __set($property, $value) {
   $this->properties[$property] = $value;
 }
}
