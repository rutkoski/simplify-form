<?php

use Simplify as s;

$config = Simplify::config();

define('FORM_DIR', preg_replace('#[\\\/]+#', '/', __dir__ . '/'));

$config['forms:dir'] = FORM_DIR;
$config['templates:path:'] = '{forms:dir}templates';

require_once('functions.php');
