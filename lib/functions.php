<?php

function sy_form_array_merge_recursive($paArray1, $paArray2)
{
  if (! is_array($paArray1) or ! is_array($paArray2)) {
    return $paArray2;
  }
  foreach ($paArray2 as $sKey2 => $sValue2) {
    $paArray1[$sKey2] = sy_form_array_merge_recursive(@$paArray1[$sKey2], $sValue2);
  }
  return $paArray1;
}

function sy_form_fix_files_array($files)
{
  if (is_array($files) && ! empty($files)) {
    $names = array(
        'name' => 'name',
        'type' => 'type',
        'tmp_name' => 'tmp_name',
        'error' => 'error',
        'size' => 'size'
    );
    
    foreach ($files as $key => &$value) {
      array_walk_recursive($value, 
          function (&$v, $k, $u)
          {
            $v = array(
                $u => $v
            );
          }, $key);
    }
    
    if (isset($files['name'])) {
      foreach ($files['name'] as $key => $v) {
        $new[$key] = array();
        
        foreach ($files as $value) {
          $new[$key] = sy_form_array_merge_recursive($new[$key], $value[$key]);
        }
      }
    }
  }
  
  return $new;
}
  