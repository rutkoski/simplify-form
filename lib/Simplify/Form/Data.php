<?php

class Simplify_Form_Data
{

  public static function copy(&$to, &$from, $index)
  {
    $args = func_get_args();
    array_splice($args, 0, 2);
    foreach ($args as &$index) {
      if (is_array($index)) {
        $index = implode(':', $index);
      }
    }
    $index = implode(':', $args);
    
    self::set($to, self::get($from, $index), $index);
  }

  public static function set(&$data, $value, $index)
  {
    $args = func_get_args();
    array_splice($args, 0, 2);
    foreach ($args as &$index) {
      if (is_array($index)) {
        $index = implode(':', $index);
      }
    }
    $index = implode(':', $args);
    
    $row = & $data;
    
    $sub = $index;
    while (($p = strpos($sub, ':')) !== false) {
      $index = substr($sub, 0, $p);
      $sub = substr($sub, $p + 1);
      
      $row = & $row[$index];
    }
    $index = $sub;
    
    $row[$index] = $value;
  }

  public static function get(&$data, $index)
  {
    $args = func_get_args();
    array_splice($args, 0, 1);
    foreach ($args as &$index) {
      if (is_array($index)) {
        $index = implode(':', $index);
      }
    }
    $index = implode(':', $args);
    
    $row = & $data;
    
    $sub = $index;
    while (($p = strpos($sub, ':')) !== false) {
      $index = substr($sub, 0, $p);
      $sub = substr($sub, $p + 1);
      
      $row = & $row[$index];
    }
    $index = $sub;
    
    return $row[$index];
  }

}
